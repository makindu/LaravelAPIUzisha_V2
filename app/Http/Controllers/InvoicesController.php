<?php

namespace App\Http\Controllers;

use App\Models\Debts;
use App\Models\Invoices;
use App\Models\DebtPayments;
use App\Models\InvoiceDetails;
use Illuminate\Support\Facades\DB;
use App\Models\StockHistoryController;
use App\Http\Requests\StoreInvoicesRequest;
use App\Http\Requests\UpdateInvoicesRequest;
use App\Models\Accounts;
use App\Models\CustomerController;
use App\Models\DepositController;
use App\Models\DepositServices;
use App\Models\DepositsUsers;
use App\Models\DetailsInvoicesStatus;
use App\Models\Expenditures;
use App\Models\invoicesdetailscolors;
use App\Models\invoicesdetailsdefects;
use App\Models\invoicesdetailsmaterials;
use App\Models\invoicesdetailsreasons;
use App\Models\invoicesdetailsSpots;
use App\Models\invoicesdetailsStyles;
use App\Models\invoicesStatus;
use App\Models\moneys;
use App\Models\OtherEntries;
use App\Models\pressingStockStory;
use App\Models\ServicesController;
use App\Models\User;
use Illuminate\Http\Request;
use stdClass;

class InvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseid)
    {
        $list=collect(Invoices::where('enterprise_id','=',$enterpriseid)->where('type_facture','!=','order')->get());
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $listdata;
    }

    /**
     * Cancelling
     */
    public function cancelling(Request $request){
        // return $request;
       $invoice=Invoices::find($request['invoice']['id']);
        if($invoice){
             //before deleting remove details
             $details=InvoiceDetails::where('invoice_id','=',$invoice->id)->get();
             foreach ($details as $detail) {
                 InvoiceDetails::destroy($detail);
             }
         //remove stock history and making returning stock
         $histories=StockHistoryController::where('invoice_id','=',$invoice->id);
         foreach($histories as $history){
             $history['type']='discount';
             $history['motif']='ristourne appliqué à la suppréssion facture';
             StockHistoryController::create($history);
             StockHistoryController::destroy($history);
         }
         //remove debts and payments raws
             $debts=Debts::where('invoice_id','=',$invoice->id)->get();
             foreach($debts as $debt){
                 $payments=DebtPayments::where('debt_payments.debt_id', '=', $debt->id)->get();
                 foreach ($payments as $payment) {
                     DebtPayments::destroy($payment);
                 }
                 Debts::destroy($debt);
             }
             
            return  $invoice->delete();
            // $deleted=$this->destroy($invoice);
            // if ($deleted) {
            //     return response()->json([
            //         'message'=>'cancelled'
            //     ]);
            // }
        }else{
            return response()->json([
                'message'=>'unknown'
            ]);
        }
    }
    

    /**
     * Report by articles
     */
     public function reportbyarticles(Request $request){
        $services=[];
        if(isset($request->from)==false && empty($request->from) && isset($request->to)==false && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if (isset($request->services) && count($request->services)>0) {
            $services=collect(ServicesController::leftjoin('categories_services_controllers as C', 'services_controllers.category_id','=','C.id')
            ->leftjoin('unit_of_measure_controllers as U','services_controllers.uom_id','=','U.id')
            ->whereIn('services_controllers.id',$request['services'])
            ->get(['services_controllers.*','C.name as category_name','U.symbol as uom_symbol']));

                $services->transform(function ($service) use ($request){
                    $mouvements=InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                                ->select(DB::raw('sum(invoice_details.quantity) as total_quantity'),DB::raw('sum(invoice_details.total) as total_sell'))
                                ->whereBetween('invoice_details.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                                ->where('invoice_details.service_id','=',$service['id'])
                                ->where('I.type_facture','<>','proforma')
                                ->get()->first();
                    $service['total_quantity']=$mouvements['total_quantity'];
                    $service['total_sell']=$mouvements['total_sell'];
                    return $service;
                });
        }

        return response()->json([
            "data"=>$services,
            "from"=>$request['from'],
            "to"=>$request['to'],
            "totalquantities"=>$services->sum('total_quantity'),
            "totalsells"=>$services->sum('total_sell'),
            "money"=>$this->defaultmoney($this->getEse($request['user_id'])['id'])
        ]);
     }    
     
     /**
     * Report sell by deposits and  articles
     */
     public function reportbydepositsarticles(Request $request){
        $deposits=[];
        $user=$this->getinfosuser($request['user_id']);
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if (isset($request['deposits']) && !empty($request['deposits'])) {
            $deposits=collect(DepositController::whereIn('id',$request['deposits'])->get());
            $deposits->transform(function ($deposit) use ($request){
                if (isset($request['services']) && !empty($request['services'])) {
                    //if there are services sent
                    $services=collect(ServicesController::leftjoin('categories_services_controllers as C', 'services_controllers.category_id','=','C.id')
                                                        ->leftjoin('unit_of_measure_controllers as U','services_controllers.uom_id','=','U.id')
                                                        ->whereIn('services_controllers.id',$request['services'])
                                                        ->get(['services_controllers.*','C.name as category_name','U.symbol as uom_symbol']));
                    $services->transform(function ($service) use ($request,$deposit){
                        $mouvements=InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                        ->select(DB::raw('sum(invoice_details.quantity) as total_quantity'),DB::raw('sum(invoice_details.total) as total_sell'))
                        ->whereBetween('invoice_details.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                        ->where('invoice_details.service_id','=',$service['id'])
                        ->where('I.type_facture','<>','proforma')
                        ->where('invoice_details.deposit_id','=',$deposit['id'])
                        ->get()->first();

                        $service['total_quantity']=$mouvements['total_quantity'];
                        $service['total_sell']=$mouvements['total_sell'];

                        return $service;

                    });
                    $deposit['services']=$services;
                    $deposit['total_quantities']=$services->sum('total_quantity');
                    $deposit['total_sells']=$services->sum('total_sell');
                }else{
                    $services=collect(InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                    ->select('invoice_details.service_id')
                    ->whereBetween('invoice_details.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                    ->where('I.type_facture','<>','proforma')
                    ->where('invoice_details.deposit_id','=',$deposit['id'])
                    ->groupByRaw('invoice_details.service_id')
                    ->get());

                    $services->transform(function($service) use ($request,$deposit){
                        $service=ServicesController::leftjoin('categories_services_controllers as C', 'services_controllers.category_id','=','C.id')
                                                        ->leftjoin('unit_of_measure_controllers as U','services_controllers.uom_id','=','U.id')
                                                        ->where('services_controllers.id','=',$service['service_id'])
                                                        ->get(['services_controllers.*','C.name as category_name','U.symbol as uom_symbol'])->first();

                        $mouvements=InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                                            ->select(DB::raw('sum(invoice_details.quantity) as total_quantity'),DB::raw('sum(invoice_details.total) as total_sell'))
                                            ->whereBetween('invoice_details.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                                            ->where('invoice_details.service_id','=',$service['id'])
                                            ->where('I.type_facture','<>','proforma')
                                            ->where('invoice_details.deposit_id','=',$deposit['id'])
                                            ->get()->first();

                        $service['total_quantity']=$mouvements['total_quantity'];
                        $service['total_sell']=$mouvements['total_sell'];

                        return $service;
                    });
                    
                    $deposit['services']=$services;
                    $deposit['total_quantities']=$services->sum('total_quantity');
                    $deposit['total_sells']=$services->sum('total_sell');
                }
                return $deposit;
            });
        }
       
        return response()->json([
            "data"=>$deposits,
            "from"=>$request['from'],
            "to"=>$request['to'],
            "totalquantities"=>$deposits->sum('total_quantities'),
            "totalsells"=>$deposits->sum('total_sells'),
            "money"=>$this->defaultmoney($this->getEse($request['user_id'])['id'])
        ]);
     }
     
     /**
     * Report sell by agents
     */
     public function reportbyagents(Request $request){
        $agents=[];
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if (isset($request['agents']) && !empty($request['agents'])) {
            $agents=collect(User::whereIn('id',$request['agents'])->select([
                'id',
                'user_name',
                'user_mail',
                'user_phone',
                'user_type',
                'status',
                'note',
                'avatar',
                'full_name'
                ])->get());

            $agents->transform(function ($agent) use($request){

                $services=collect(InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                ->select('invoice_details.service_id')
                ->whereBetween('invoice_details.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->where('I.type_facture','<>','proforma')
                ->where('I.edited_by_id','=',$agent['id'])
                ->groupByRaw('invoice_details.service_id')
                ->get());

                $services->transform(function ($service) use ($request,$agent){
                    $service=ServicesController::leftjoin('categories_services_controllers as C', 'services_controllers.category_id','=','C.id')
                    ->leftjoin('unit_of_measure_controllers as U','services_controllers.uom_id','=','U.id')
                    ->where('services_controllers.id','=',$service['service_id'])
                    ->get(['services_controllers.*','C.name as category_name','U.symbol as uom_symbol'])->first();

                    $mouvements=InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                    ->select(DB::raw('sum(invoice_details.quantity) as total_quantity'),DB::raw('sum(invoice_details.total) as total_sell'))
                    ->whereBetween('invoice_details.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                    ->where('invoice_details.service_id','=',$service['id'])
                    ->where('I.type_facture','<>','proforma')
                    ->where('I.edited_by_id','=',$agent['id'])
                    ->get()->first();

                    $service['total_quantity']=$mouvements['total_quantity'];
                    $service['total_sell']=$mouvements['total_sell'];

                    return $service;
                });

                $agent['services']=$services;
                $agent['total_quantities']=$services->sum('total_quantity');
                $agent['total_sells']=$services->sum('total_sell');
                return $agent;
            }); 
        }
       
        return response()->json([
            "data"=>$agents,
            "from"=>$request['from'],
            "to"=>$request['to'],
            "totalquantities"=>$agents->sum('total_quantities'),
            "totalsells"=>$agents->sum('total_sells'),
            "money"=>$this->defaultmoney($this->getEse($request['user_id'])['id'])
        ]);
     }

    /**
     * report cash book by user 
     */
    public function cashbook(Request $request){
        $list_data=[];
        $user=$this->getinfosuser($request['user_id']);
        $enterprise=$this->getEse($user['id']);
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if ($user['user_type']=='super_admin') {
            
           $entries=OtherEntries::leftjoin('accounts as A','other_entries.account_id','=','A.id')
           ->where('other_entries.enterprise_id','=',$enterprise['id'])
           ->whereBetween('other_entries.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
           ->get(['A.name','other_entries.*']);

           $withdraw=Expenditures::leftjoin('accounts as A','expenditures.account_id','=','A.id')
           ->where('expenditures.enterprise_id','=',$enterprise['id'])
           ->whereBetween('expenditures.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
           ->get(['A.name','expenditures.*']);

            $list_data['entries']=$entries;
            $list_data['sum_entries']=$entries->sum('amount');
            $list_data['withdraw']=$withdraw;
            $list_data['sum_withdraw']=$withdraw->sum('amount');
            $list_data['total']=$withdraw->sum('amount')+$entries->sum('amount');

        }else{

            $entries=OtherEntries::leftjoin('accounts as A','other_entries.account_id','=','A.id')
            ->where('other_entries.user_id','=',$request['user_id'])
            ->where('other_entries.enterprise_id','=',$enterprise['id'])
            ->whereBetween('other_entries.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->get(['A.name','other_entries.*']);
 
            $withdraw=Expenditures::leftjoin('accounts as A','expenditures.account_id','=','A.id')
            ->where('expenditures.user_id','=',$request['user_id'])
            ->where('expenditures.enterprise_id','=',$enterprise['id'])
            ->whereBetween('expenditures.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->get(['A.name','expenditures.*']);
 
             $list_data['entries']=$entries;
             $list_data['sum_entries']=$entries->sum('amount');
             $list_data['withdraw']=$withdraw;
             $list_data['sum_withdraw']=$withdraw->sum('amount');
             $list_data['total']=$withdraw->sum('amount')+$entries->sum('amount');
        } 

        return response()->json([
            'data'=>$list_data,
            'from'=>$request['from'],
            'to'=>$request['to'],
            'money'=>$this->defaultmoney($enterprise['id'])]);
    }

    /**
     * report by user for selling cash and credit
     */
    public function reportUserSelling(Request $request){
        $list_data=[];
        $user=$this->getinfosuser($request['user_id']);
        $enterprise=$this->getEse($user['id']);
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if ($user['user_type']=='super_admin') {
            $users=Invoices::where('enterprise_id','=',$enterprise['id'])
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->select('edited_by_id')
            ->groupBy('edited_by_id')
            ->get();
            
            foreach ($users as $user) {
                $cash=Invoices::select(DB::raw('sum(total) as totalCash'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['edited_by_id'])->where('type_facture','=','cash')->get('totalCash')->first();
                $credits=Invoices::select(DB::raw('sum(total) as totalCredits'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['edited_by_id'])->where('type_facture','=','credit')->get('totalCredits')->first();
                $user['user']=$this->getinfosuser($user['edited_by_id']);
                $user['cash']=$cash['totalCash'];
                $user['credits']=$credits['totalCredits'];

                //grouped details invoices
                $invoices=Invoices::where('edited_by_id','=',$user['edited_by_id'])
                ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->get();
                $details_gotten=[];
                foreach ($invoices as $invoice) {
                    $details= DB::table('invoice_details')
                    ->leftjoin('services_controllers as S','invoice_details.service_id','=','S.id')
                    ->leftjoin('unit_of_measure_controllers as UOM','S.uom_id','=','UOM.id')
                    ->where('invoice_details.invoice_id','=',$invoice['id'])
                    ->select('invoice_details.service_id','S.name','UOM.symbol','invoice_details.quantity','invoice_details.total')
                    ->get();
                    foreach ($details as $detail) {
                        array_push($details_gotten,$detail);
                    }
                    
                    // $details_gotten=collect($details_gotten)->mergeRecursive($details);
                }
                // $grouped=$details_gotten->groupBy('name');
                $user['details']=$details_gotten;
                // $user['details']=$details_gotten->all();
                array_push($list_data,$user); 
            }
        }else{
            $users=Invoices::where('enterprise_id','=',$enterprise['id'])->where('edited_by_id','=',$request['user_id'])
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->select('edited_by_id')
            ->groupBy('edited_by_id')
            ->get();
            
            foreach ($users as $user) {
                $cash=Invoices::select(DB::raw('sum(total) as totalCash'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['edited_by_id'])->where('type_facture','=','cash')->get('totalCash')->first();
                $credits=Invoices::select(DB::raw('sum(total) as totalCredits'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['edited_by_id'])->where('type_facture','=','credit')->get('totalCredits')->first();
                $user['user']=$this->getinfosuser($user['edited_by_id']);
                $user['cash']=$cash['totalCash'];
                $user['credits']=$credits['totalCredits'];

                //grouped details invoices
                $invoices=Invoices::where('edited_by_id','=',$user['edited_by_id'])
                ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->get();
                $details_gotten=[];
                foreach ($invoices as $invoice) {
                    $details= DB::table('invoice_details')
                    ->leftjoin('services_controllers as S','invoice_details.service_id','=','S.id')
                    ->leftjoin('unit_of_measure_controllers as UOM','S.uom_id','=','UOM.id')
                    ->where('invoice_details.invoice_id','=',$invoice['id'])
                    ->select('invoice_details.service_id','S.name','UOM.symbol','invoice_details.quantity','invoice_details.total')
                    ->get();
                    foreach ($details as $detail) {
                        array_push($details_gotten,$detail);
                    }
                    
                    // $details_gotten=collect($details_gotten)->mergeRecursive($details);
                }
                // $grouped=$details_gotten->groupBy('name');
                $user['details']=$details_gotten;
                // $user['details']=$details_gotten->all();
                array_push($list_data,$user); 
            }
        } 

        return response()->json([
            'data'=>$list_data,
            'from'=>$request['from'],
            'to'=>$request['to'],
            'money'=>$this->defaultmoney($enterprise['id'])]);
    }
    
    /**
     * report by user for selling edited
     */
    public function reportUserSelling2(Request $request){
        $users=[];
        $actualUser=$this->getinfosuser($request['user_id']);
        $enterprise=$this->getEse($actualUser['id']);
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if ($actualUser['user_type']=='super_admin') {

            $users=collect(Invoices::where('enterprise_id','=',$enterprise['id'])
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->select('edited_by_id')
            ->groupByRaw('edited_by_id')
            ->get());

            $users->transform(function ($agent) use ($request){
                $user=User::where('id','=',$agent['edited_by_id'])->select(['id','user_name','user_mail','user_phone','user_type','status','note','avatar','full_name'])->get()->first();

                $cash=Invoices::select(DB::raw('sum(total) as totalCash'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','=','cash')->get('totalCash')->first();
                $credits=Invoices::select(DB::raw('sum(total) as totalCredits'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','=','credit')->get('totalCredits')->first();
                $vat=Invoices::select(DB::raw('sum(vat_amount) as totalVatAmount'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','<>','proforma')->get('totalVatAmount')->first();
                $ttc=Invoices::select(DB::raw('sum(netToPay) as total_ttc'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','<>','proforma')->get('total_ttc')->first();
                $user['cash']=$cash['totalCash'];
                $user['credits']=$credits['totalCredits'];
                $user['total_ht']=$credits['totalCredits']+$cash['totalCash'];
                $user['total_ttc']=$ttc['total_ttc'];
                $user['totalVatAmount']=$vat['totalVatAmount'];
                $user['sold']=$cash['totalCash']+$vat['totalVatAmount'];

                $services=collect(InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                ->select('invoice_details.service_id')
                ->whereBetween('invoice_details.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->where('I.type_facture','<>','proforma')
                ->where('I.edited_by_id','=',$user['id'])
                ->groupByRaw('invoice_details.service_id')
                ->get());
                $services->transform(function ($service) use ($request){
                    $service=ServicesController::leftjoin('categories_services_controllers as C', 'services_controllers.category_id','=','C.id')
                    ->leftjoin('unit_of_measure_controllers as U','services_controllers.uom_id','=','U.id')
                    ->where('services_controllers.id','=',$service['service_id'])
                    ->get(['services_controllers.*','C.name as category_name','U.symbol as uom_symbol'])->first();

                    $mouvements=InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                    ->select(DB::raw('sum(invoice_details.quantity) as total_quantity'),DB::raw('sum(invoice_details.total) as total_sell'))
                    ->whereBetween('invoice_details.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                    ->where('invoice_details.service_id','=',$service['id'])
                    ->where('I.type_facture','<>','proforma')
                    ->get()->first();

                    $service['total_quantity']=$mouvements['total_quantity'];
                    $service['total_sell']=$mouvements['total_sell'];
                    return $service;
                });

                $user['details']=$services;

                return $user;
            });
        }else{
            //if not super admin
            $usersSent=[];
            array_push($usersSent,$request['user_id']);
            $users=collect(Invoices::where('enterprise_id','=',$enterprise['id'])
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->whereIn('edited_by_id',[$usersSent])
            ->select('edited_by_id')
            ->groupByRaw('edited_by_id')
            ->get());

            $users->transform(function ($agent) use ($request){

                $user=User::where('id','=',$agent['edited_by_id'])->select(['id','user_name','user_mail','user_phone','user_type','status','note','avatar','full_name'])->get()->first();

                $cash=Invoices::select(DB::raw('sum(total) as totalCash'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','=','cash')->get('totalCash')->first();
                $credits=Invoices::select(DB::raw('sum(total) as totalCredits'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','=','credit')->get('totalCredits')->first();
                $vat=Invoices::select(DB::raw('sum(vat_amount) as totalVatAmount'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','<>','proforma')->get('totalVatAmount')->first();
                $ttc=Invoices::select(DB::raw('sum(netToPay) as total_ttc'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','<>','proforma')->get('total_ttc')->first();
                $user['cash']=$cash['totalCash'];
                $user['credits']=$credits['totalCredits'];
                $user['total_ht']=$credits['totalCredits']+$cash['totalCash'];
                $user['total_ttc']=$ttc['total_ttc'];
                $user['totalVatAmount']=$vat['totalVatAmount'];
                $user['sold']=$cash['totalCash']+$vat['totalVatAmount'];

                $services=collect(InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                ->select('invoice_details.service_id')
                ->whereBetween('invoice_details.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->where('I.type_facture','<>','proforma')
                ->where('I.edited_by_id','=',$user['id'])
                ->groupByRaw('invoice_details.service_id')
                ->get());
                $services->transform(function ($service) use ($request){
                    $service=ServicesController::leftjoin('categories_services_controllers as C', 'services_controllers.category_id','=','C.id')
                    ->leftjoin('unit_of_measure_controllers as U','services_controllers.uom_id','=','U.id')
                    ->where('services_controllers.id','=',$service['service_id'])
                    ->get(['services_controllers.*','C.name as category_name','U.symbol as uom_symbol'])->first();

                    $mouvements=InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                    ->select(DB::raw('sum(invoice_details.quantity) as total_quantity'),DB::raw('sum(invoice_details.total) as total_sell'))
                    ->whereBetween('invoice_details.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                    ->where('invoice_details.service_id','=',$service['id'])
                    ->where('I.type_facture','<>','proforma')
                    ->get()->first();

                    $service['total_quantity']=$mouvements['total_quantity'];
                    $service['total_sell']=$mouvements['total_sell'];
                    return $service;
                });

                $user['details']=$services;

                return $user;
            });
        } 

        return response()->json([
            'data'=>$users,
            'from'=>$request['from'],
            'to'=>$request['to'],
            'subtot_ht'=>$users->sum('total_ht'),
            'subtot_ttc'=>$users->sum('total_ttc'),
            'subtot_tva'=>$users->sum('totalVatAmount'),
            'subtot_cash'=>$users->sum('cash'),
            'subtot_credits'=>$users->sum('credits'),
            'subtot_sold'=>$users->sum('sold'),
            'money'=>$this->defaultmoney($enterprise['id'])]);
    }
    
     /**
     * report by user for selling cash and credit
     */
    public function reportUserSellingGroupByArticle(Request $request){
        $list_data=[];
        $user=$this->getinfosuser($request['user_id']);
        $enterprise=$this->getEse($user['id']);
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if ($user['user_type']=='super_admin') {
            $users=Invoices::where('enterprise_id','=',$enterprise['id'])
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->select('edited_by_id')
            ->groupBy('edited_by_id')
            ->get();
            
            foreach ($users as $user) {
                $cash=Invoices::select(DB::raw('sum(total) as totalCash'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['edited_by_id'])->where('type_facture','=','cash')->get('totalCash')->first();
                $credits=Invoices::select(DB::raw('sum(total) as totalCredits'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['edited_by_id'])->where('type_facture','=','credit')->get('totalCredits')->first();
                $user['user']=$this->getinfosuser($user['edited_by_id']);
                $user['cash']=$cash['totalCash'];
                $user['credits']=$credits['totalCredits'];

                //grouped details invoices
                $invoices=Invoices::where('edited_by_id','=',$user['edited_by_id'])
                ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->get();
                $details_gotten=[];
                foreach ($invoices as $invoice) {
                    
                    $details=InvoiceDetails::where('invoice_details.invoice_id','=',$invoice['id'])
                    ->select('invoice_details.service_id')
                    ->get();
                    foreach ($details as $value) {
                        array_push($details_gotten,$value);
                    }
                }
    
                $user['details']=collect($details_gotten)->mapToGroups(function($item){
                    return ["service"=>$item['service_id']];
                });
            
                array_push($list_data,$user); 
            }
        }else{
            $users=Invoices::where('enterprise_id','=',$enterprise['id'])->where('edited_by_id','=',$request['user_id'])
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->select('edited_by_id')
            ->groupBy('edited_by_id')
            ->get();
            
            foreach ($users as $user) {
                $cash=Invoices::select(DB::raw('sum(total) as totalCash'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['edited_by_id'])->where('type_facture','=','cash')->get('totalCash')->first();
                $credits=Invoices::select(DB::raw('sum(total) as totalCredits'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['edited_by_id'])->where('type_facture','=','credit')->get('totalCredits')->first();
                $user['user']=$this->getinfosuser($user['edited_by_id']);
                $user['cash']=$cash['totalCash'];
                $user['credits']=$credits['totalCredits'];

                //grouped details invoices
                $invoices=Invoices::where('edited_by_id','=',$user['edited_by_id'])
                ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->get();
                $details_gotten=[];
                foreach ($invoices as $invoice) {
                    $details= DB::table('invoice_details')
                    ->leftjoin('services_controllers as S','invoice_details.service_id','=','S.id')
                    ->leftjoin('unit_of_measure_controllers as UOM','S.uom_id','=','UOM.id')
                    ->where('invoice_details.invoice_id','=',$invoice['id'])
                    ->select('invoice_details.service_id','S.name','UOM.symbol','invoice_details.quantity','invoice_details.total')
                    ->get();
                    foreach ($details as $detail) {
                        array_push($details_gotten,$detail);
                    }
                }
                $user['details']=$details_gotten;
                array_push($list_data,$user); 
            }
        } 

        return response()->json([
            'data'=>$list_data,
            'from'=>$request['from'],
            'to'=>$request['to'],
            'money'=>$this->defaultmoney($enterprise['id'])]);
    }

     /**
      * for a specific users
      */
    public function foraspecificuser(Request $request){

        if(isset($request['from']) && !empty($request['from']) && isset($request['to']) && !empty($request['to'])){
            $list=collect(Invoices::where('edited_by_id','=',$request->user_id)
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->get());
            $listdata=$list->map(function ($item,$key){
                return $this->show($item);
            });
            return $listdata;
        }
        else{
            $from=date('Y-m-d');
            $list=collect(Invoices::where('edited_by_id','=',$request->user_id)
            ->whereBetween('created_at',[$from.' 00:00:00',$from.' 23:59:59'])->get());
            $listdata=$list->map(function ($item,$key){
                return $this->show($item);
            });
            return $listdata;
        }
      
    }

    public function enterpriseorders($enterpriseid){

        $list=collect(Invoices::where('enterprise_id','=',$enterpriseid)->where('type_facture','=','order')->get());
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $listdata;
    }

    public function userorders($user_id){

        $list=collect(Invoices::where('edited_by_id','=',$user_id)->where('type_facture','=','order')->get());
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $listdata;
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreInvoicesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInvoicesRequest $request)
    {
        $User=$this->getinfosuser($request['edited_by_id']);
        $Ese=$this->getEse($request['edited_by_id']);
        if($User && $Ese){
            if($this->isactivatedEse($Ese['id'])){
                return $this->saveInvoice($request);
            }else{
                //count numbers of invoices done
                $sumInvoices =Invoices::select(DB::raw('count(*) as number'))->where('enterprise_id','=',$Ese['id'])->get('number')->first();
                if ($sumInvoices['number']>=100) {
                    return response()->json([
                        'data' =>'',
                        'message'=>'invoices number exceeded'
                    ]);
                }else{
                    return $this->saveInvoice($request);
                }
            }
        }else{
            return response()->json([
                'data' =>'',
                'message'=>'user unknown'
            ]); 
        }
    }

    public function saveInvoice(StoreInvoicesRequest $request){
             
            $request['uuid']=$this->getUuId('F','C');
            $invoice=Invoices::create($request->all());
            //enregistrement des details
            if(isset($request->details)){
                foreach ($request->details as $detail) {
                    $detail['invoice_id']=$invoice['id'];
                    $detail['total']=$detail['quantity']*$detail['price'];
                    InvoiceDetails::create($detail);
                    if((isset($request->type_facture) && $request->type_facture=='cash') || (isset($request->type_facture) && $request->type_facture=='credit') )
                    {
                        if(isset($detail['type_service']) && $detail['type_service']=='1'){
                            $stockbefore=DepositServices::where('deposit_id','=',$detail['deposit_id'])->where('service_id','=',$detail['service_id'])->get()[0];
                            DB::update('update deposit_services set available_qte = available_qte - ? where service_id = ? and deposit_id = ?',[$detail['quantity'],$detail['service_id'],$detail['deposit_id']]);
                            
                            StockHistoryController::create([
                                'service_id'=>$detail['service_id'],
                                'user_id'=>$invoice['edited_by_id'],
                                'invoice_id'=>$invoice['id'],
                                'quantity'=>$detail['quantity'],
                                'price'=>$detail['price'],
                                'type'=>'withdraw',
                                'type_approvement'=>$invoice['type_facture'],
                                'enterprise_id'=>$request['enterprise_id'],
                                'motif'=>'vente',
                                'depot_id'=>$detail['deposit_id'],
                                'quantity_before'=>$stockbefore->available_qte,
                            ]);
                        }
                    }
                }
            }
            //check if debt
            if($invoice['type_facture']=='credit'){
                if($invoice['customer_id']>0){
                    $debt=Debts::create([
                        'created_by_id'=>$invoice['edited_by_id'],
                        'customer_id'=>$invoice['customer_id'],
                        'invoice_id'=>$invoice['id'],
                        'status'=>'0',
                        'amount'=>$invoice['netToPay']-$invoice['amount_paid'],
                        'sold'=>$invoice['netToPay']-$invoice['amount_paid'],
                        'uuid'=>$this->getUuId('D','C'),
                        'sync_status'=>'1'
                    ]);

                    //if there is amount paid creating a payment
                    if ($invoice['amount_paid']>0) {
                        DebtPayments::create([
                            'done_by_id'=>$invoice['edited_by_id'],
                            'debt_id'=>$debt['id'],
                            'amount_payed'=>$invoice['amount_paid'],
                            'uuid'=>$this->getUuId('P','C')
                        ]);
                    } 
                }
            }

            return response()->json([
                'data' =>$this->show($invoice),
                'message'=>'can make invoice'
            ]);
    }

    /**
     * Saving Offline invoices
     */
    public function storebySafeGuard(StoreInvoicesRequest $request){
        $User=$this->getinfosuser($request['invoice']['edited_by_id']);
        $Ese=$this->getEse($request['invoice']['edited_by_id']);
        if($User && $Ese){
            if($this->isactivatedEse($Ese['id'])){
                return $this->saveOfflineInvoice($request);
            }else{
                //count numbers of invoices done
                $sumInvoices =Invoices::select(DB::raw('count(*) as number'))->where('enterprise_id','=',$Ese['id'])->get('number')->first();
                if ($sumInvoices['number']>=100) {
                    return response()->json([
                        'data' =>'',
                        'message'=>'invoices number exceeded'
                    ]);
                }else{
                    return $this->saveOfflineInvoice($request);
                }
            }
        }else{
            return response()->json([
                'data' =>'',
                'message'=>'user unknown'
            ]); 
        }
    }

    /**
     * SaveOffline Invoice
     */
    public function saveOfflineInvoice(StoreInvoicesRequest $request){
 
        // if(empty($request['invoice']['customer_id'])){
        //     // if (isset($request['invoice']['customer_uuid']) && !empty($request['invoice']['customer_uuid'])){
        //     //     # code...
        //     // }
        //     $customer=CustomerController::where('uuid','=',$request['invoice']['customer_uuid'])->get()->first();
        //     $request['invoice']['customer_id']=$customer->id;
        // }
        $invoice=Invoices::create($request['invoice']);
        
        //enregistrement des details
        if(isset($request->details)){
            foreach ($request->details as $detail) {
                $detail['invoice_id']=$invoice['id'];
                $detail['total']=$detail['quantity']*$detail['price'];
                InvoiceDetails::create($detail);
            }
        }
        return response()->json([
            'data' =>$this->show($invoice),
            'message'=>'can make invoice'
        ]);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function show(Invoices $invoices)
    {
        $details=[];
        $debt=[];
        $payments=[];

        $details=InvoiceDetails::leftjoin('moneys as M','invoice_details.money_id','=','M.id')
        ->leftjoin('services_controllers','invoice_details.service_id','=','services_controllers.id')
        ->leftjoin('unit_of_measure_controllers as UOM','services_controllers.uom_id','=','UOM.id')
        ->where('invoice_details.invoice_id','=',$invoices->id)
        ->get(['UOM.name as uom_name','UOM.symbol as uom_symbol','M.money_name','M.abreviation','services_controllers.name as service_name','services_controllers.description','invoice_details.*']);

        $invoice=Invoices::leftjoin('customer_controllers as C', 'invoices.customer_id','=','C.id')
        ->leftjoin('moneys as M', 'invoices.money_id','=','M.id')
        ->leftjoin('users as U', 'invoices.edited_by_id','=','U.id')
        ->leftjoin('tables as T', 'invoices.table_id','=','T.id')
        ->leftjoin('servants as S', 'invoices.servant_id','=','S.id')
        ->where('invoices.id', '=', $invoices->id)
        ->get(['T.id as table_id','T.name as table_name','S.id as servant_id','S.name as servant_name','M.abreviation','M.money_name','U.user_name','U.full_name','C.phone','C.mail','C.adress','C.customerName as customer_name','invoices.*'])[0];

        $debt=Debts::join('invoices as I','debts.invoice_id','=','I.id')
        ->leftjoin('moneys as M','I.money_id','=','M.id')
        ->leftjoin('customer_controllers as C','I.customer_id','=','C.id')
        ->where('invoice_id','=',$invoices->id)
        ->get(['M.money_name','M.abreviation','C.phone','C.mail','C.adress','C.customerName','I.uuid as invoiceUuid','I.total as invoice_total_amount','I.amount_paid as invoice_amount_paid','debts.*']);
        if(count($debt)>0){
            $payments=DebtPayments::where('debt_payments.debt_id', '=', $debt[0]['id'])->get();
        }
       
      
        // return ['invoice'=>$invoice,'details'=>$details];
        return ['invoice'=>$invoice,'details'=>$details,'debt'=>$debt,'payments'=>$payments];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function edit(Invoices $invoices)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInvoicesRequest  $request
     * @param  \App\Models\Invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInvoicesRequest $request, Invoices $invoices)
    {
       return $invoices->update($request->all());
    }

    /**
     * get for a specific customer
     */
    public function foracustomer($customerid){
        $list=collect(Invoices::where('customer_id','=',$customerid)->get());
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $listdata;
    }  
    
    public function forACustomerFiltered(Request $request){
        if (empty($request['from']) && empty($request['to'])) {
            $request['from']=date('Y-m-d');
            $request['to']=date('Y-m-d');
        } 

        $list=collect(Invoices::whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('customer_id','=',$request['customer_id'])->get());
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return ["invoices"=>$listdata,"from"=> $request['from'],"to"=> $request['to']];
    }

    /**
     * compte courant for a specific customer
     */
    public function comptecourant($customerid){

        $list=collect(Invoices::where('customer_id','=',$customerid)->where('type_facture','=','credit')->get());
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $listdata;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoices $invoices)
    {
        //before deleting remove details
            $details=InvoiceDetails::where('invoice_id','=',$invoices->id)->get();
            foreach ($details as $detail) {
                InvoiceDetails::destroy($detail);
            }
        //remove stock history and making returning stock
        $histories=StockHistoryController::where('invoice_id','=',$invoices->id);
        foreach($histories as $history){
            $history['type']='discount';
            $history['motif']='ristourne appliqué à la suppréssion facture';
            StockHistoryController::create($history);
            StockHistoryController::destroy($history);
        }
        //remove debts and payments raws
            $debts=Debts::where('invoice_id','=',$invoices->id)->get();
            foreach($debts as $debt){
                $payments=DebtPayments::where('debt_payments.debt_id', '=', $debt->id)->get();
                foreach ($payments as $payment) {
                    DebtPayments::destroy($payment);
                }
                Debts::destroy($debt);
            }
            
      return  Invoices::destroy($invoices);
    }

    //Pressings

    /**
     * new order
     */
    public function storeorder(Request $request){
        $User=$this->getinfosuser($request['edited_by_id']);
        $Ese=$this->getEse($request['edited_by_id']);
        if($User && $Ese){
            if($this->isactivatedEse($Ese['id'])){
                return $this->saveOrder($request);
            }else{
                //count numbers of invoices done
                $sumInvoices =Invoices::select(DB::raw('count(*) as number'))->where('enterprise_id','=',$Ese['id'])->get('number')->first();
                if ($sumInvoices['number']>=100) {
                    return response()->json([
                        'data' =>'',
                        'message'=>'invoices number exceeded'
                    ]);
                }else{
                    return $this->saveOrder($request);
                }
            }
        }else{
            return response()->json([
                'data' =>'',
                'message'=>'user unknown'
            ]); 
        }
    }

    /**
     * orders
     */
    //update order
    public function updateorder(Request $request){
        $response = new stdClass;
        if (isset($request['id']) && !empty($request['id'])) {
            $find= Invoices::find($request['id']);
            if($find){
                DB::update('update invoices set status=? where id = ? ',[$request['status']]);
            }
        }
    }

    public function saveOrder(Request $request){
       
        $request['uuid']=$this->getUuId('PF','C');
        $invoice=Invoices::create($request->all());
        if ($invoice) {
            $message="can make invoice";
            //saving details
            if(isset($request->details)){
                foreach ($request->details as  $detail) {
                    $detail['invoice_id']=$invoice['id'];
                    $detail['total']=$detail['quantity']*$detail['price'];
                    $detailCreated=InvoiceDetails::create($detail);
                    if($detailCreated){
                        //creating colors for actual detail
                        if (isset($detail['colors']) && !empty($detail['colors'])) {
                            foreach ($detail['colors'] as $color) {
                                $color['detail_id']=$detailCreated->id;
                                if (empty($color['observation'])) {
                                    $color['observation']="aucune";
                                }
                                invoicesdetailscolors::create($color);
                            } 
                        }

                        //creating defects for actual detail
                        if (isset($detail['defects']) && !empty($detail['defects'])) {
                            foreach ($detail['defects'] as $defect) {
                                $defect['detail_id']=$detailCreated->id;
                                if (empty($defect['observation'])) {
                                    $defect['observation']="aucune";
                                }
                                invoicesdetailsdefects::create($defect);
                            } 
                        } 

                        //creating spots for actual detail
                        if (isset($detail['spots']) && !empty($detail['spots'])) {
                            foreach ($detail['spots'] as $spot) {
                                $spot['detail_id']=$detailCreated->id;
                                if (empty($spot['observation'])) {
                                    $spot['observation']="aucune";
                                }
                                invoicesdetailsSpots::create($spot);
                            } 
                        } 

                        // //creating materials for actual detail
                        // if (isset($detail['materials']) && !empty($detail['materials'])) {
                        //     foreach ($detail['materials'] as $material) {
                        //         $material['detail_id']=$detailCreated->id;
                        //         if (empty($material['observation'])) {
                        //             $material['observation']="aucune";
                        //         }
                        //         invoicesdetailsmaterials::create($material);
                        //     } 
                        // } 

                        //creating reasons for actual detail
                        if (isset($detail['reasons']) && !empty($detail['reasons'])) {
                            foreach ($detail['reasons'] as $reason) {
                                $reason['detail_id']=$detailCreated->id;
                                if (empty($reason['observation'])) {
                                    $reason['observation']="aucune";
                                }
                                invoicesdetailsreasons::create($reason);
                            } 
                        }  
                        
                        //creating styles for actual detail
                        if (isset($detail['styles']) && !empty($detail['styles'])) {
                            foreach ($detail['styles'] as $style) {
                                $style['detail_id']=$detailCreated->id;
                                if (empty($style['observation'])) {
                                    $style['observation']="aucune";
                                }
                                invoicesdetailsStyles::create($style);
                            } 
                        }

                        //creating stock stories
                        pressingStockStory::create([
                            'deposit_id'=>$detailCreated['deposit_id'],
                            'service_id'=>$detailCreated['service_id'],
                            'done_by'=>$invoice['edited_by_id'],
                            'customer_id'=>$invoice['customer_id'],
                            'invoice_id'=>$invoice['id'],
                            'detail_invoice_id'=>$detailCreated['id'],
                            'quantity'=>$detailCreated['quantity'],
                            'price'=>$detailCreated['price'],
                            'total'=>$detailCreated['price']*$detailCreated['quantity'],
                            'sold'=>$detailCreated['quantity'],
                            'note'=>"",
                            'type'=>'entry',
                            'status'=>"machine",
                            'uuid'=>$this->getUuId('PS','C'),
                            'enterprise_id'=>$invoice['enterprise_id']
                        ]);
                    }else{
                        $message="details not created";
                    }
                }
            }
            //creating debt if necessary
            if($invoice['total']>$invoice['amount_paid'] && isset($invoice['customer_id']) && $invoice['customer_id']>0){
                Debts::create([
                    'created_by_id'=>$invoice['edited_by_id'],
                    'customer_id'=>$invoice['customer_id'],
                    'invoice_id'=>$invoice['id'],
                    'status'=>'0',
                    'amount'=>$invoice['total']-$invoice['amount_paid'],
                    'sold'=>$invoice['total']-$invoice['amount_paid'],
                    'uuid'=>$this->getUuId('PD','C'),
                    'sync_status'=>'1'
                ]);
            }
        }else{
            $message="error occurred";
        }
        
        return response()->json([
            'data' =>$this->ShowInvoicePressing($invoice),
            'message'=>$message
        ]);
    }
    /**
     * get orders
     */
    public function pressingOrders(Request $request){
        $user=$this->getinfosuser($request['user_id']);
        $ese=$this->getEse($request['user_id']);

        if(isset($request['from']) && !empty($request['from']) && isset($request['to']) && !empty($request['to'])){
            $list=collect(Invoices::where('edited_by_id','=',$request->user_id)
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->get());
            $listdata=$list->map(function ($item,$key){
                return $this->ShowInvoicePressing($item);
            });
            return $listdata;
        }
        else{
            $from=date('Y-m-d');
            $list=collect(Invoices::where('edited_by_id','=',$request->user_id)
            ->whereBetween('created_at',[$from.' 00:00:00',$from.' 23:59:59'])->get());
            $listdata=$list->map(function ($item,$key){
                return $this->ShowInvoicePressing($item);
            });
            return $listdata;
        }

        // $list=collect(Invoices::where('type_facture','!=','proforma')->where('status','=','0')->where('enterprise_id','=',$ese['id'])->get());
        // $listdata=$list->map(function ($item){
        //     return $this->ShowInvoicePressing($item);
        // });
        // return $listdata;
    }

    /**
     * show pressing method
     */
    public function ShowInvoicePressing(Invoices $invoices){
        $details=[];
        $debt=[];
        $payments=[];

        $details=InvoiceDetails::leftjoin('moneys as M','invoice_details.money_id','=','M.id')
        ->leftjoin('services_controllers','invoice_details.service_id','=','services_controllers.id')
        ->leftjoin('unit_of_measure_controllers as UOM','services_controllers.uom_id','=','UOM.id')
        ->where('invoice_details.invoice_id','=',$invoices->id)
        ->get(['UOM.name as uom_name','UOM.symbol as uom_symbol','M.money_name','M.abreviation','services_controllers.name as service_name','invoice_details.*']);

        foreach ($details as $value) {
            //getting others informations for each detail
            $value['colors']=invoicesdetailscolors::join('colors','invoicesdetailscolors.color_id','=','colors.id')->where('invoicesdetailscolors.detail_id','=',$value['id'])->get(['colors.*','invoicesdetailscolors.quantity']);
            $value['defects']=invoicesdetailsdefects::join('defects','invoicesdetailsdefects.defect_id','=','defects.id')->where('invoicesdetailsdefects.detail_id','=',$value['id'])->get(['defects.*','invoicesdetailsdefects.quantity']);
            $value['spots']=invoicesdetailsSpots::join('spots','invoicesdetails_spots.spot_id','=','spots.id')->where('invoicesdetails_spots.detail_id','=',$value['id'])->get(['spots.*','invoicesdetails_spots.quantity']);
            $value['materials']=invoicesdetailsmaterials::join('materials','invoicesdetailsmaterials.material_id','=','materials.id')->where('invoicesdetailsmaterials.detail_id','=',$value['id'])->get(['materials.*','invoicesdetailsmaterials.quantity']);
            $value['reasons']=invoicesdetailsreasons::join('reasons','invoicesdetailsreasons.reason_id','=','reasons.id')->where('invoicesdetailsreasons.detail_id','=',$value['id'])->get(['reasons.*','invoicesdetailsreasons.quantity']);
            $value['styles']=invoicesdetailsStyles::join('styles','invoicesdetails_styles.style_id','=','styles.id')->where('invoicesdetails_styles.detail_id','=',$value['id'])->get(['styles.*','invoicesdetails_styles.quantity']);
            $value['status']=DetailsInvoicesStatus::join('statuses as ST','details_invoices_statuses.status_id','=','ST.id')->where('detail_id','=',$value['id'])->get('ST.*')->last();
        }

        $debt=Debts::join('invoices as I','debts.invoice_id','=','I.id')
        ->leftjoin('moneys as M','I.money_id','=','M.id')
        ->leftjoin('customer_controllers as C','I.customer_id','=','C.id')
        ->where('invoice_id','=',$invoices->id)
        ->get(['M.money_name','M.abreviation','C.customerName','I.uuid as invoiceUuid','I.total as invoice_total_amount','I.amount_paid as invoice_amount_paid','debts.*']);
        if(count($debt)>0){
            $payments=DebtPayments::where('debt_payments.debt_id', '=', $debt[0]['id'])->get();
        }
        $invoices['debt']=$debt;
        if (isset($invoices['money_id']) && !empty($invoices['money_id']) && $invoices['money_id']>0) {
            $invoices['money']=moneys::find($invoices['money_id']);   
        } 
        if (isset($invoices['customer_id']) && !empty($invoices['customer_id']) && $invoices['customer_id']>0) {
            $invoices['customer']=CustomerController::find($invoices['customer_id']);   
        }

        $invoices['payments']=$payments;
        $invoices['details']=$details;
        //new code
        $invoices['status']=invoicesStatus::join('statuses as ST','invoices_statuses.status_id','=','ST.id')->where('invoice_id','=',$invoices['id'])->get('ST.*')->last();
        //end new code
        return $invoices;
    }
}
