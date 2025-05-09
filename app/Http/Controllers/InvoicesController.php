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
use App\Models\Bonus;
use App\Models\CustomerController;
use App\Models\customerspointshistory;
use App\Models\DepositController;
use App\Models\DepositServices;
use App\Models\detailinvoicesubservices;
use App\Models\DetailsInvoicesStatus;
use App\Models\Enterprises;
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
use App\Models\licences;
use Carbon\Carbon;
use Illuminate\Http\Request;
use stdClass;
use Exception;

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
     * get invoice by Id
     */
    public function getinvoicebyid($invoiceid){
        try{
            return response()->json([
                "message"=>"success",
                "status"=>200,
                "error"=>null,
                "data"=>$this->show(Invoices::find($invoiceid))
            ]);
            
        }catch(Exception $e){
            return response()->json([
                "message"=>"error",
                "status"=>200,
                "error"=>$e->getMessage(),
                "data"=>null
            ]);
        }  
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
                $detail->delete();
             }
         //remove stock history and making returning stock
         $histories=StockHistoryController::where('invoice_id','=',$invoice->id)->get();
         foreach($histories as $history){
             $history['type']='discount';
             $history['motif']='ristourne appliqué à la suppréssion facture';
             StockHistoryController::create($history);
             //update the qty
             $history->delete();
         }
         //remove debts and payments raws
             $debts=Debts::where('invoice_id','=',$invoice->id)->get();
             foreach($debts as $debt){
                 $payments=DebtPayments::where('debt_payments.debt_id', '=', $debt->id)->get();
                 foreach ($payments as $payment) {
                    $payment->delete();
                 }
                $debt->delete();
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
     * Report by articles based on dates operations
     */
     public function reportbyarticlesbasedondates(Request $request){
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
                                ->whereBetween('I.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
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
     * Report by articles based on date operation
     */
     public function reportbyarticlesbasedondateoperation(Request $request){
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
                                ->whereBetween('I.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
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
     * Report sell by deposits and  articles
     */
     public function reportbydepositsarticlesbasedondateoperation(Request $request){
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
                        ->whereBetween('I.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
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
                    ->whereBetween('I.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
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
                                            ->whereBetween('I.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
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
     * Report sell by agents based on date operation
     */
     public function reportbyagentsbasedondateoperations(Request $request){
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
                ->whereBetween('I.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
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
                    ->whereBetween('I.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
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
           ->whereBetween('other_entries.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
           ->get(['A.name','other_entries.*','other_entries.done_at as created_at']);

           $withdraw=Expenditures::leftjoin('accounts as A','expenditures.account_id','=','A.id')
           ->where('expenditures.enterprise_id','=',$enterprise['id'])
           ->whereBetween('expenditures.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
           ->get(['A.name','expenditures.*','expenditures.done_at as created_at']);

            $list_data['entries']=$entries;
            $list_data['sum_entries']=$entries->sum('amount');
            $list_data['withdraw']=$withdraw;
            $list_data['sum_withdraw']=$withdraw->sum('amount');
            $list_data['total']=$withdraw->sum('amount')+$entries->sum('amount');

        }else{

            $entries=OtherEntries::leftjoin('accounts as A','other_entries.account_id','=','A.id')
            ->where('other_entries.user_id','=',$request['user_id'])
            ->where('other_entries.enterprise_id','=',$enterprise['id'])
            ->whereBetween('other_entries.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->get(['A.name','other_entries.*']);
 
            $withdraw=Expenditures::leftjoin('accounts as A','expenditures.account_id','=','A.id')
            ->where('expenditures.user_id','=',$request['user_id'])
            ->where('expenditures.enterprise_id','=',$enterprise['id'])
            ->whereBetween('expenditures.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
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
     * report cash book by user based on date operation
     */
    public function cashbookbasedondateoperations(Request $request){
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
           ->whereBetween('other_entries.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
           ->get(['A.name','other_entries.*','other_entries.done_at as created_at']);

           $withdraw=Expenditures::leftjoin('accounts as A','expenditures.account_id','=','A.id')
           ->where('expenditures.enterprise_id','=',$enterprise['id'])
           ->whereBetween('expenditures.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
           ->get(['A.name','expenditures.*','expenditures.done_at as created_at']);

            $list_data['entries']=$entries;
            $list_data['sum_entries']=$entries->sum('amount');
            $list_data['withdraw']=$withdraw;
            $list_data['sum_withdraw']=$withdraw->sum('amount');
            $list_data['total']=$withdraw->sum('amount')+$entries->sum('amount');

        }else{

            $entries=OtherEntries::leftjoin('accounts as A','other_entries.account_id','=','A.id')
            ->where('other_entries.user_id','=',$request['user_id'])
            ->where('other_entries.enterprise_id','=',$enterprise['id'])
            ->whereBetween('other_entries.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->get(['A.name','other_entries.*']);
 
            $withdraw=Expenditures::leftjoin('accounts as A','expenditures.account_id','=','A.id')
            ->where('expenditures.user_id','=',$request['user_id'])
            ->where('expenditures.enterprise_id','=',$enterprise['id'])
            ->whereBetween('expenditures.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
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
            ->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->select('edited_by_id')
            ->groupByRaw('edited_by_id')
            ->get());

            $users->transform(function ($agent) use ($request){
                $user=User::where('id','=',$agent['edited_by_id'])->select(['id','user_name','user_mail','user_phone','user_type','status','note','avatar','full_name'])->get()->first();

                $cash=Invoices::select(DB::raw('sum(total) as totalCash'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','=','cash')->get('totalCash')->first();
                $credits=Invoices::select(DB::raw('sum(total) as totalCredits'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','=','credit')->get('totalCredits')->first();
                $vat=Invoices::select(DB::raw('sum(vat_amount) as totalVatAmount'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','<>','proforma')->get('totalVatAmount')->first();
                $ttc=Invoices::select(DB::raw('sum(netToPay) as total_ttc'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','<>','proforma')->get('total_ttc')->first();
                $user['cash']=$cash['totalCash'];
                $user['credits']=$credits['totalCredits'];
                $user['total_ht']=$credits['totalCredits']+$cash['totalCash'];
                $user['total_ttc']=$ttc['total_ttc'];
                $user['totalVatAmount']=$vat['totalVatAmount'];
                $user['sold']=$cash['totalCash']+$vat['totalVatAmount'];

                $listdetails=collect(InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                ->select('invoice_details.service_id')
                ->whereBetween('invoice_details.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->where('I.type_facture','<>','proforma')
                ->where('I.edited_by_id','=',$user['id'])
                ->groupByRaw('invoice_details.service_id')
                ->get());
                $request['user_id']=$user['id'];
                $services=$listdetails->transform(function ($service) use ($request,$user){
                    $service=ServicesController::leftjoin('categories_services_controllers as C', 'services_controllers.category_id','=','C.id')
                    ->leftjoin('unit_of_measure_controllers as U','services_controllers.uom_id','=','U.id')
                    ->where('services_controllers.id','=',$service['service_id'])
                    ->get(['services_controllers.*','C.name as category_name','U.symbol as uom_symbol'])->first();

                    $mouvements=InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                    ->select(DB::raw('sum(invoice_details.quantity) as total_quantity'),DB::raw('sum(invoice_details.total) as total_sell'))
                    ->whereBetween('invoice_details.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                    ->where('invoice_details.service_id','=',$service['id'])
                    ->where('I.edited_by_id','=',$user['id'])
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
            ->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->where('edited_by_id','=',$request['user_id'])
            ->select('edited_by_id')
            ->groupByRaw('edited_by_id')
            ->get());

            $users->transform(function ($agent) use ($request){

                $user=User::where('id','=',$agent['edited_by_id'])->select(['id','user_name','user_mail','user_phone','user_type','status','note','avatar','full_name'])->get()->first();
                $request['user_id']=$user['id'];
                $cash=Invoices::select(DB::raw('sum(total) as totalCash'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','=','cash')->get('totalCash')->first();
                $credits=Invoices::select(DB::raw('sum(total) as totalCredits'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','=','credit')->get('totalCredits')->first();
                $vat=Invoices::select(DB::raw('sum(vat_amount) as totalVatAmount'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','<>','proforma')->get('totalVatAmount')->first();
                $ttc=Invoices::select(DB::raw('sum(netToPay) as total_ttc'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','<>','proforma')->get('total_ttc')->first();
                $user['cash']=$cash['totalCash'];
                $user['credits']=$credits['totalCredits'];
                $user['total_ht']=$credits['totalCredits']+$cash['totalCash'];
                $user['total_ttc']=$ttc['total_ttc'];
                $user['totalVatAmount']=$vat['totalVatAmount'];
                $user['sold']=$cash['totalCash']+$vat['totalVatAmount'];

                $services=collect(InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                ->select('invoice_details.service_id')
                ->whereBetween('invoice_details.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
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
                    ->whereBetween('invoice_details.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                    ->where('invoice_details.service_id','=',$service['id'])
                    ->where('I.edited_by_id','=',$request['user_id'])
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
     * report by user for selling based on dates operations
     */
    public function reportUserSelling2basedondatesoperations(Request $request){
        $users=[];
        $actualUser=$this->getinfosuser($request['user_id']);
        $enterprise=$this->getEse($actualUser['id']);
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if ($actualUser['user_type']=='super_admin') {

            $users=collect(Invoices::where('enterprise_id','=',$enterprise['id'])
            ->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->select('edited_by_id')
            ->groupByRaw('edited_by_id')
            ->get());

            $users->transform(function ($agent) use ($request){
                $user=User::where('id','=',$agent['edited_by_id'])->select(['id','user_name','user_mail','user_phone','user_type','status','note','avatar','full_name'])->get()->first();

                $cash=Invoices::select(DB::raw('sum(total) as totalCash'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','=','cash')->get('totalCash')->first();
                $credits=Invoices::select(DB::raw('sum(total) as totalCredits'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','=','credit')->get('totalCredits')->first();
                $vat=Invoices::select(DB::raw('sum(vat_amount) as totalVatAmount'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','<>','proforma')->get('totalVatAmount')->first();
                $ttc=Invoices::select(DB::raw('sum(netToPay) as total_ttc'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','<>','proforma')->get('total_ttc')->first();
            
                $user['sold']=$cash['totalCash'];$user['cash']=$cash['totalCash'];
                $user['credits']=$credits['totalCredits'];
                $user['total_ht']=$credits['totalCredits']+$cash['totalCash'];
                $user['total_ttc']=$ttc['total_ttc'];
                $user['totalVatAmount']=$vat['totalVatAmount'];
                $user['sold']=$cash['totalCash'];

                $listdetails=collect(InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                ->select('invoice_details.service_id')
                ->whereBetween('invoice_details.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->where('I.type_facture','<>','proforma')
                ->where('I.edited_by_id','=',$user['id'])
                ->groupByRaw('invoice_details.service_id')
                ->get());
                $request['user_id']=$user['id'];
                $services=$listdetails->transform(function ($service) use ($request,$user){
                    $service=ServicesController::leftjoin('categories_services_controllers as C', 'services_controllers.category_id','=','C.id')
                    ->leftjoin('unit_of_measure_controllers as U','services_controllers.uom_id','=','U.id')
                    ->where('services_controllers.id','=',$service['service_id'])
                    ->get(['services_controllers.*','C.name as category_name','U.symbol as uom_symbol'])->first();

                    $mouvements=InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                    ->select(DB::raw('sum(invoice_details.quantity) as total_quantity'),DB::raw('sum(invoice_details.total) as total_sell'))
                    ->whereBetween('I.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                    ->where('invoice_details.service_id','=',$service['id'])
                    ->where('I.edited_by_id','=',$user['id'])
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
            ->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->where('edited_by_id','=',$request['user_id'])
            ->select('edited_by_id')
            ->groupByRaw('edited_by_id')
            ->get());

            $users->transform(function ($agent) use ($request){

                $user=User::where('id','=',$agent['edited_by_id'])->select(['id','user_name','user_mail','user_phone','user_type','status','note','avatar','full_name'])->get()->first();
                $request['user_id']=$user['id'];
                $cash=Invoices::select(DB::raw('sum(total) as totalCash'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','=','cash')->get('totalCash')->first();
                $credits=Invoices::select(DB::raw('sum(total) as totalCredits'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','=','credit')->get('totalCredits')->first();
                $vat=Invoices::select(DB::raw('sum(vat_amount) as totalVatAmount'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','<>','proforma')->get('totalVatAmount')->first();
                $ttc=Invoices::select(DB::raw('sum(netToPay) as total_ttc'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','<>','proforma')->get('total_ttc')->first();
                
                $user['sold']=$cash['totalCash'];$user['cash']=$cash['totalCash'];
                $user['credits']=$credits['totalCredits'];
                $user['total_ht']=$credits['totalCredits']+$cash['totalCash'];
                $user['total_ttc']=$ttc['total_ttc'];
                $user['totalVatAmount']=$vat['totalVatAmount'];
                $user['sold']=$cash['totalCash'];

                $services=collect(InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                ->select('invoice_details.service_id')
                ->whereBetween('invoice_details.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
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
                    ->whereBetween('invoice_details.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                    ->where('invoice_details.service_id','=',$service['id'])
                    ->where('I.edited_by_id','=',$request['user_id'])
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
     * report by user for selling edited filtered by tva
     */
    public function reportUserSelling2filteredbytva(Request $request){
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

            if($request['filter']=='vat'){
                $users->transform(function ($agent) use ($request){
                    $user=User::where('id','=',$agent['edited_by_id'])->select(['id','user_name','user_mail','user_phone','user_type','status','note','avatar','full_name'])->get()->first();
    
                 
                    $user['cash']=0;
                    $user['credits']=0;
                    $user['total_ht']=0;
                    $user['total_ttc']=0;
                    $user['totalVatAmount']=0;
                    $user['sold']=0;
    
                    $listdetails=collect(InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                    ->join('services_controllers as S','invoice_details.service_id','S.id')
                    ->select('invoice_details.service_id')
                    ->whereBetween('invoice_details.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                    ->where('I.type_facture','<>','proforma')
                    ->where('I.edited_by_id','=',$user['id'])
                    ->where('S.has_vat','=',true)
                    ->groupByRaw('invoice_details.service_id')
                    ->get());
                    $request['user_id']=$user['id'];
                    $services=$listdetails->transform(function ($service) use ($request,$user){
                        $service=ServicesController::leftjoin('categories_services_controllers as C', 'services_controllers.category_id','=','C.id')
                        ->leftjoin('unit_of_measure_controllers as U','services_controllers.uom_id','=','U.id')
                        ->where('services_controllers.id','=',$service['service_id'])
                        ->get(['services_controllers.*','C.name as category_name','U.symbol as uom_symbol'])->first();
    
                        $mouvements=InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                        ->join('services_controllers as S','invoice_details.service_id','S.id')
                        ->select(DB::raw('sum(invoice_details.quantity) as total_quantity'),DB::raw('sum(invoice_details.total) as total_sell'))
                        ->whereBetween('invoice_details.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                        ->where('invoice_details.service_id','=',$service['id'])
                        ->where('I.edited_by_id','=',$user['id'])
                        ->where('S.has_vat','=',true)
                        ->where('I.type_facture','<>','proforma')
                        ->get()->first();
    
                        $service['total_quantity']=$mouvements['total_quantity'];
                        $service['total_sell']=$mouvements['total_sell'];
                        $user['sold']=$user['sold']+$mouvements['total_sell'];
                        return $service;
                    });
    
                    $user['details']=$services;
    
                    return $user;
                });
        
            }else{
                $users->transform(function ($agent) use ($request){
                    $user=User::where('id','=',$agent['edited_by_id'])->select(['id','user_name','user_mail','user_phone','user_type','status','note','avatar','full_name'])->get()->first();

                    $user['cash']=0;
                    $user['credits']=0;
                    $user['total_ht']=0;
                    $user['total_ttc']=0;
                    $user['totalVatAmount']=0;
                    $user['sold']=0;
    
                    $listdetails=collect(InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                    ->join('services_controllers as S','invoice_details.service_id','S.id')
                    ->select('invoice_details.service_id')
                    ->whereBetween('invoice_details.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                    ->where('I.type_facture','<>','proforma')
                    ->where('I.edited_by_id','=',$user['id'])
                    ->where('S.has_vat','=',false)
                    ->groupByRaw('invoice_details.service_id')
                    ->get());
                    $request['user_id']=$user['id'];
                    $services=$listdetails->transform(function ($service) use ($request,$user){
                        $service=ServicesController::leftjoin('categories_services_controllers as C', 'services_controllers.category_id','=','C.id')
                        ->leftjoin('unit_of_measure_controllers as U','services_controllers.uom_id','=','U.id')
                        ->where('services_controllers.id','=',$service['service_id'])
                        ->get(['services_controllers.*','C.name as category_name','U.symbol as uom_symbol'])->first();
    
                        $mouvements=InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                        ->join('services_controllers as S','invoice_details.service_id','S.id')
                        ->select(DB::raw('sum(invoice_details.quantity) as total_quantity'),DB::raw('sum(invoice_details.total) as total_sell'))
                        ->whereBetween('invoice_details.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                        ->where('invoice_details.service_id','=',$service['id'])
                        ->where('I.edited_by_id','=',$user['id'])
                        ->where('S.has_vat','=',false)
                        ->where('I.type_facture','<>','proforma')
                        ->get()->first();
    
                        $service['total_quantity']=$mouvements['total_quantity'];
                        $service['total_sell']=$mouvements['total_sell'];
                        $user['sold']=$user['sold']+$mouvements['total_sell'];
                        return $service;
                    });
    
                    $user['details']=$services;
    
                    return $user;
                });
            }

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
     * report by user for selling edited
     */
    public function reportUserSellingwithoutdetails(Request $request){
        $users=[];
        $actualUser=$this->getinfosuser($request['user_id']);
        $enterprise=$this->getEse($actualUser['id']);
        $fidelityreport=null;
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if ($actualUser['user_type']=='super_admin') {
                //fidelity report
            $userctrl = new UsersController();
            $fidelityreport=$userctrl->superadminfidelityreport($request,$actualUser['id']);

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
            'fidelityreport'=>$fidelityreport,
            'money'=>$this->defaultmoney($enterprise['id'])]);
    }

    /**
     * report grouped by dates
     */
    public function sellsreportgroupedbydates(Request $request){
        $intervals=[];
        $datatoreturns=[];
        $fromdate=Carbon::parse($request['from']);
        $todate=Carbon::parse($request['to']);

        while($fromdate<=$todate){
            array_push($intervals,$fromdate->toDateString());
            $fromdate->addDay();
        }
        $cumul=$this->reportUserSellingwithoutdetailsbasedoperationdates($request)->original;
        foreach ($intervals as $dateoperation) {
            $request['from']=$dateoperation;
            $request['to']=$dateoperation;
            $data=$this->reportUserSelling2basedondatesoperations($request)->original;
            array_push($datatoreturns,$data);
        }
    
        return response()->json([
            "cumul"=>$cumul,
            "details"=>$datatoreturns
        ]) ;
    }

    /**
     * report by user for selling edited
     */
    public function reportUserSellingwithoutdetailsbasedoperationdates(Request $request){
        $users=[];
        $actualUser=$this->getinfosuser($request['user_id']);
        $enterprise=$this->getEse($actualUser['id']);
        $fidelityreport=null;
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if ($actualUser['user_type']=='super_admin') {
                //fidelity report
            $userctrl = new UsersController();
            $fidelityreport=$userctrl->superadminfidelityreport($request,$actualUser['id']);

            $users=collect(Invoices::where('enterprise_id','=',$enterprise['id'])
            ->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->select('edited_by_id')
            ->groupByRaw('edited_by_id')
            ->get());

            $users->transform(function ($agent) use ($request){
                $user=User::where('id','=',$agent['edited_by_id'])->select(['id','user_name','user_mail','user_phone','user_type','status','note','avatar','full_name'])->get()->first();
                $cash=Invoices::select(DB::raw('sum(netToPay) as totalCash'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','=','cash')->get('totalCash')->first();
                $credits=Invoices::select(DB::raw('sum(netToPay) as totalCredits'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','=','credit')->get('totalCredits')->first();
                $vat=Invoices::select(DB::raw('sum(vat_amount) as totalVatAmount'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','<>','proforma')->get('totalVatAmount')->first();
                // $ttc=Invoices::select(DB::raw('sum(netToPay) as total_ttc'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','<>','proforma')->get('total_ttc')->first();
                $user['cash']=$cash['totalCash'];
                $user['credits']=$credits['totalCredits'];
                $user['total_ht']=$credits['totalCredits']+$cash['totalCash'];
                $user['totalVatAmount']=$vat['totalVatAmount'];
                $user['total_ttc']=$user['credits']+$user['cash']+$user['totalVatAmount'];
                $user['sold']=$user['cash']+$user['totalVatAmount'];
                return $user;
            });
        }else{
            //if not super admin
            $usersSent=[];
            array_push($usersSent,$request['user_id']);
            $users=collect(Invoices::where('enterprise_id','=',$enterprise['id'])
            ->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->whereIn('edited_by_id',[$usersSent])
            ->select('edited_by_id')
            ->groupByRaw('edited_by_id')
            ->get());

            $users->transform(function ($agent) use ($request){

                $user=User::where('id','=',$agent['edited_by_id'])->select(['id','user_name','user_mail','user_phone','user_type','status','note','avatar','full_name'])->get()->first();

                $cash=Invoices::select(DB::raw('sum(total) as totalCash'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','=','cash')->get('totalCash')->first();
                $credits=Invoices::select(DB::raw('sum(total) as totalCredits'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','=','credit')->get('totalCredits')->first();
                $vat=Invoices::select(DB::raw('sum(vat_amount) as totalVatAmount'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','<>','proforma')->get('totalVatAmount')->first();
                $ttc=Invoices::select(DB::raw('sum(netToPay) as total_ttc'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('edited_by_id','=',$user['id'])->where('type_facture','<>','proforma')->get('total_ttc')->first();
                $user['cash']=$cash['totalCash'];
                $user['credits']=$credits['totalCredits'];
                $user['total_ht']=$credits['totalCredits']+$cash['totalCash'];
                $user['total_ttc']=$ttc['total_ttc'];
                $user['totalVatAmount']=$vat['totalVatAmount'];
                $user['sold']=$cash['totalCash']+$vat['totalVatAmount'];

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
            'fidelityreport'=>$fidelityreport,
            'money'=>$this->defaultmoney($enterprise['id'])]);
    }

    /**
     * grouped sell report by prices
     */
    public function groupreportbyprices(Request $request){
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
                $user['user']=$this->getinfosuser($user['edited_by_id']);
                $prices=collect(InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                // ->leftjoin('prices_categories as PC','invoice_details.price','=','PC.price')
                ->where('I.edited_by_id','=',$user['edited_by_id'])
                ->whereBetween('I.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->select('invoice_details.price')
                ->groupBy('invoice_details.price')
                ->get());

                $prices=$prices->map(function ($price) use ($request,$user){
                    $servicesgrouped=collect(InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                                    ->where('I.edited_by_id','=',$user['edited_by_id'])
                                    ->where('invoice_details.price','=',$price['price'])
                                    ->whereBetween('I.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                                    ->select('invoice_details.service_id')
                                    ->groupBy('invoice_details.service_id')
                                    ->get());

                    $services=$servicesgrouped->map(function ($service) use ($request,$user,$price){
                        $details=InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                                ->select(DB::raw('sum(invoice_details.quantity) as totalquantities'),DB::raw('sum(invoice_details.total) as totalprices'))
                                ->whereBetween('I.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                                ->where('I.edited_by_id','=',$user['edited_by_id'])
                                ->where('I.type_facture','<>','proforma')
                                ->where('invoice_details.service_id','=',$service['service_id'])
                                ->where('invoice_details.price','=',$price['price'])
                                ->get(['totalquantities','totalprices'])
                                ->first();
                        $service['totalquantities']=$details['totalquantities'];
                        $service['totalprices']=$details['totalprices'];
                        $service['service_detail']=servicesController::leftjoin('unit_of_measure_controllers as UOM','services_controllers.uom_id','=','UOM.id')
                                                                        ->where('services_controllers.id','=',$service['service_id'])
                                                                        ->get(['services_controllers.*','UOM.symbol','UOM.name as uom_name'])->first();
                        return $service;
                    });
                    $price['label']="";
                    $price['details']=$services;
                    return $price;
                });
                $user['prices']=$prices;
                
                array_push($list_data,$user); 
            }
        } 

        return response()->json([
            'cumul'=>$this->reportsellservicescumul($request),
            'data'=>$list_data,
            'from'=>$request['from'],
            'to'=>$request['to'],
            'money'=>$this->defaultmoney($enterprise['id'])]);

    }

    /**
     * cumul report cumule prices
     */
    public function reportsellservicescumul(Request $request){
        $list_data= new stdclass;
        $user=$this->getinfosuser($request['user_id']);
        $enterprise=$this->getEse($user['id']);
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if ($enterprise && $user['user_type']=='super_admin') {

            $cumulprices=collect(InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                        // ->leftjoin('prices_categories as PC','invoice_details.price','=','PC.price')
                        ->where('I.enterprise_id','=',$enterprise['id'])
                        ->whereBetween('I.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                        ->select('invoice_details.price')
                        ->groupBy('invoice_details.price')
                        ->get());

                $cumulprices=$cumulprices->map(function ($price) use ($request,$enterprise){
                    $cumulservices=collect(InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                                    ->where('I.enterprise_id','=',$enterprise['id'])
                                    ->where('invoice_details.price','=',$price['price'])
                                    ->whereBetween('I.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                                    ->select('invoice_details.service_id')
                                    ->groupBy('invoice_details.service_id')
                                    ->get());

                    $servicescumulative=$cumulservices->map(function ($service) use ($request,$price,$enterprise){
                        $details=InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                                ->select(DB::raw('sum(invoice_details.quantity) as totalquantities'),DB::raw('sum(invoice_details.total) as totalprices'))
                                ->where('I.enterprise_id','=',$enterprise['id'])
                                ->whereBetween('I.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                                ->where('I.type_facture','<>','proforma')
                                ->where('invoice_details.service_id','=',$service['service_id'])
                                ->where('invoice_details.price','=',$price['price'])
                                ->get(['totalquantities','totalprices'])
                                ->first();
                        $service['totalquantities']=$details['totalquantities'];
                        $service['totalprices']=$details['totalprices'];
                        $service['service_detail']=servicesController::leftjoin('unit_of_measure_controllers as UOM','services_controllers.uom_id','=','UOM.id')
                                                                        ->where('services_controllers.id','=',$service['service_id'])
                                                                        ->get(['services_controllers.*','UOM.symbol','UOM.name as uom_name'])->first();
                        return $service;
                    });
                    $price['label']="";
                    $price['details']=$servicescumulative;
                    return $price;
                });
            $list_data=$cumulprices;
        }

        return $list_data;
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
        $actualuser=$this->getinfosuser($request->user_id);
        if ($actualuser['user_type']=='super_admin') {
            if(isset($request['from']) && !empty($request['from']) && isset($request['to']) && !empty($request['to'])){
                $list=collect(Invoices::where('enterprise_id','=',$this->getEse($request->user_id)['id'])
                ->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->get());
                $listdata=$list->map(function ($item,$key){
                    return $this->show($item);
                });
                return $listdata;
            }
            else{
                $from=date('Y-m-d');
                $list=collect(Invoices::where('enterprise_id','=',$this->getEse($request->user_id)['id'])
                ->whereBetween('date_operation',[$from.' 00:00:00',$from.' 23:59:59'])->get());
                $listdata=$list->map(function ($item,$key){
                    return $this->show($item);
                });
                return $listdata;
            }
        } else {
            if(isset($request['from']) && !empty($request['from']) && isset($request['to']) && !empty($request['to'])){
                $list=collect(Invoices::where('edited_by_id','=',$request->user_id)
                ->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->get());
                $listdata=$list->map(function ($item,$key){
                    return $this->show($item);
                });
                return $listdata;
            }
            else{
                $from=date('Y-m-d');
                $list=collect(Invoices::where('edited_by_id','=',$request->user_id)
                ->whereBetween('date_operation',[$from.' 00:00:00',$from.' 23:59:59'])->get());
                $listdata=$list->map(function ($item,$key){
                    return $this->show($item);
                });
                return $listdata;
            }
        }
    }    
    
    /**
      * searching invoices for a specific users
      */
    public function searchingforaspecificuser(Request $request){
      
        $searchTerm = $request->query('keyword', '');
        $enterpriseId = $request->query('enterprise_id', 0);  
        $actualuser=$this->getinfosuser($request->query('user_id'));
        if ($actualuser['user_type']=='super_admin') {
            
            $list = Invoices::query()
                ->leftJoin('customer_controllers', 'invoices.customer_id', '=', 'customer_controllers.id')
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('services_controllers', 'invoice_details.service_id', '=', 'services_controllers.id')
                ->where('invoices.enterprise_id', '=', $enterpriseId)
                ->where(function($query) use ($searchTerm) {
                    $query->where('invoices.uuid', 'LIKE', "%$searchTerm%")
                        ->orWhere('invoices.netToPay', 'LIKE', "%$searchTerm%")
                        ->orWhere('invoices.total', 'LIKE', "%$searchTerm%")
                        ->orWhere('customer_controllers.customerName', 'LIKE', "%$searchTerm%")
                        ->orWhere('customer_controllers.phone', 'LIKE', "%$searchTerm%")
                        ->orWhere('customer_controllers.mail', 'LIKE', "%$searchTerm%")
                        ->orWhere('services_controllers.name', 'LIKE', "%$searchTerm%")
                        ->orWhere('services_controllers.description', 'LIKE', "%$searchTerm%")
                        ->orWhere('services_controllers.codebar', 'LIKE', "%$searchTerm%");
                })
                ->select('invoices.*')
                ->paginate(10)
                ->appends($request->query());


            $list->getCollection()->transform(function ($item){
                return $this->show($item);
            });
            return $list;

        } else {
            $list = Invoices::query()
                ->leftJoin('customer_controllers', 'invoices.customer_id', '=', 'customer_controllers.id')
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('services_controllers', 'invoice_details.service_id', '=', 'services_controllers.id')
                ->where('invoices.edited_by_id', '=',$actualuser['id'])
                ->where(function($query) use ($searchTerm) {
                    $query->where('invoices.uuid', 'LIKE', "%$searchTerm%")
                        ->orWhere('invoices.netToPay', 'LIKE', "%$searchTerm%")
                        ->orWhere('invoices.total', 'LIKE', "%$searchTerm%")
                        ->orWhere('customer_controllers.customerName', 'LIKE', "%$searchTerm%")
                        ->orWhere('customer_controllers.phone', 'LIKE', "%$searchTerm%")
                        ->orWhere('customer_controllers.mail', 'LIKE', "%$searchTerm%")
                        ->orWhere('services_controllers.name', 'LIKE', "%$searchTerm%")
                        ->orWhere('services_controllers.description', 'LIKE', "%$searchTerm%")
                        ->orWhere('services_controllers.codebar', 'LIKE', "%$searchTerm%");
                })
                ->select('invoices.*')
                ->paginate(10)
                ->appends($request->query());


            $list->getCollection()->transform(function ($item){
                return $this->show($item);
            });
            return $list;

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

    public function storemobile(Request $request){
        // return $request;
        $invoice=Invoices::create($request->all());
        return $invoice;
        // return response()->json([
        //     'data'=>$request,
        //     'message'=>"success",
        //     'status'=>200
        // ]) ;
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
                return $this->saveInvoiceWithBonusCalculation($request);
            }else{
                //count numbers of invoices done
                $sumInvoices =Invoices::select(DB::raw('count(*) as number'))->where('enterprise_id','=',$Ese['id'])->get('number')->first();
                if ($sumInvoices['number']>=100) {
                    return response()->json([
                        'data' =>'',
                        'message'=>'invoices number exceeded'
                    ]);
                }else{
                    return $this->saveInvoiceWithBonusCalculation($request);
                }
            }
        }else{
            return response()->json([
                'data' =>'',
                'message'=>'user unknown'
            ]); 
        }
    }

    public function saveInvoiceWithBonusCalculation(StoreInvoicesRequest $request){
          // return $request;
          $request['uuid']=$this->getUuId("F","C");
          if(!isset($request['date_operation']) && empty($request['date_operation'])){
              $request['date_operation']=date('Y-m-d');
          }

          $invoice=Invoices::create($request->all());
          $ese=$this->getEse($request['edited_by_id']);
          $fidelitymode=$ese['fidelitydefaultmode'];
          $fidelitypointvalue=$ese['fidelitypointvalue'];
          $fidelityinitvalue=$ese['initvaluefidelity'];

          //enregistrement des details
          if(isset($request->details)){

              foreach ($request->details as $detail) {
                  $detail['invoice_id']=$invoice['id'];
                  $detail['total']=$detail['quantity']*$detail['price'];
                  $detail['point']=ServicesController::find($detail['service_id'])['point'];
                  $newdetail=InvoiceDetails::create($detail);
                  if((isset($request->type_facture) && $request->type_facture=='cash') || (isset($request->type_facture) && $request->type_facture=='credit') )
                  {
                      if(isset($detail['type_service']) && $detail['type_service']=='1'){
                          $stockbefore=DepositServices::where('deposit_id','=',$detail['deposit_id'])->where('service_id','=',$detail['service_id'])->get()[0];
                          DB::update('update deposit_services set available_qte = available_qte - ? where service_id = ? and deposit_id = ?',[$detail['quantity'],$detail['service_id'],$detail['deposit_id']]);
                          //calcul stock used by FIFO or LIFO method par ici... avant d'enregistrer le stock history
                          
                         $newstockhistory= StockHistoryController::create([
                              'service_id'=>$detail['service_id'],
                              'user_id'=>$invoice['edited_by_id'],
                              'invoice_id'=>$invoice['id'],
                              'quantity'=>$detail['quantity'],
                              'price'=>$detail['price'],
                              'type'=>'withdraw',
                              'type_approvement'=>$invoice['type_facture'],
                              'enterprise_id'=>$request['enterprise_id'],
                              'motif'=>'vente',
                              'done_at'=>$invoice['date_operation'],
                              'date_operation'=>$invoice['date_operation'],
                              'uuid'=>$this->getUuId('C','ST'),
                              'depot_id'=>$detail['deposit_id'],
                              'quantity_before'=>$stockbefore->available_qte,
                              'total'=> $detail['total'],
                          ]);
                          $this->withdrawadjust($detail['deposit_id'],$detail['quantity'],$detail['price'],$newstockhistory->id,$detail['service_id']);
                      }
                  }
                  //if detail has subservices(accomp)
                  if(isset($detail['subservices']) && count($detail['subservices'])>0){
                      foreach ($detail['subservices'] as $accomp) {
                          detailinvoicesubservices::create([
                              'service_id'=>$accomp['service_id'],
                              'detail_invoice_id'=>$newdetail['id'],
                              'invoice_id'=>$invoice['id'],
                              'quantity'=>$accomp['quantity'],
                              'price'=>$accomp['price'],
                              'total'=>$accomp['quantity']*$accomp['price'],
                              'note'=>$accomp['note']
                          ]);
                      }
                  }
              }
          }



          if($invoice['type_facture']=='point' &&  $invoice['customer_id']>0){
              $count=$invoice['netToPay'];
              $constant=$fidelityinitvalue;
              $point=$count/$constant;

              $customer=CustomerController::find($invoice['customer_id']);

              $customerupdated=DB::update('update customer_controllers set totalpoints = totalpoints - ? where id = ?',[$point,$customer['id']]);
          }

          if($invoice['type_facture']=='bonus' && $invoice['customer_id']>0){
           $totalBonusPay =$invoice['netToPay'];
           $customer=CustomerController::find($invoice['customer_id']);
           $totalBonusAvailable = $customer->totalbonus - $totalBonusPay;
           if($totalBonusAvailable){
               $customer->update(['totalbonus'=> $customer->totalbonus- $totalBonusPay]);
           }else{
               $customer->update(['totalbonus'=> 0]);
               // ici il faut mettre une logic pour faire ou signifier au customer que son bonus a ete insufisant
               // pour payer la totalite de sa commande
           }

           $bonusCustomer = Bonus::where('customer_id', $invoice['customer_id'])
                           ->whereColumn('amount', '>', 'amount_used')
                           ->get();
                           foreach ($bonusCustomer as $bonus){
                               $bonusRestante = $bonus->amount - $bonus->amount_used;
                               if($totalBonusPay>= $bonusRestante){
                                   $bonus->update(['amount_used'=> $bonus->amount]);
                                   $totalBonusPay -=$bonusRestante;
                                   if($totalBonusPay==0) break;
                               }else{
                                   $bonus->update(['amount_used'=>$bonus->amount_used +$totalBonusPay  ]);
                                   break;
                               }
                           }
               //logique pour reduire les bonus du client par ici...
          }

          if($fidelitymode=='point' && $invoice['type_facture']=='cash' && $invoice['customer_id']>0){
              $count=$invoice['netToPay'];
              $constant=$fidelityinitvalue;
              if($count>=$constant){
                      $customer=CustomerController::find($invoice['customer_id']);
                      $point=($count/$constant);
                      // number_format();
                      $customerupdated=DB::update('update customer_controllers set totalpoints = totalpoints + ? where id = ?',[$point,$customer['id']]);
                      if($customerupdated){
                           //creating fidelity history ligne
                           customerspointshistory::create([
                               'customer_id'=>$customer['id'],
                               'invoice_id'=>$invoice['id'],
                               'point'=>$point,
                               'type'=>'point',
                               'value'=>$ese['fidelitypointvalue']*$point,
                               'used'=>false,
                               'done_at'=>$invoice['date_operation']
                           ]);
                      }
              }
          }

          if($fidelitymode=='bonus' && $invoice['type_facture']=='cash' && $invoice['customer_id']>0){
               //put the code behind this bonus's logic
               //   totalbonus

               // $bonus =0;

               // $customer->update(['totalbonus'=>$bonus]);
               $customer= CustomerController::find($invoice['customer_id']);
               $countService =0;
               $initvalue = Enterprises::find($request['enterprise_id']);
               $initvaluefidelity = $initvalue->initvaluefidelity;
               $date_from_fidelity =$initvalue->date_from_fidelity;
               $date_to_fidelity =$initvalue->date_to_fidelity;
               if(isset($request->details)){

                   DB::beginTransaction();
                   try {

                   foreach ($request->details as $detail) {
                       $detail['invoice_id']=$invoice['id'];
                       $countService =0; //toujours init countservice ici
                       if((isset($request->type_facture) && $request->type_facture=='cash')  )
                       {
                           // || (isset($request->type_facture) && $request->type_facture=='credit')
                           if(isset($detail['type_service']) && $detail['type_service']=='1'){
                                
                               $lastbonusInvoice = Bonus::where('customer_id',$invoice['customer_id'] )
                                                           ->where('service_id', $detail['service_id'])
                                                           ->orderBy('id', 'desc')
                                                           ->first();
                               // ici on recupere toutes les quantites de ces articles dependant de customer id et de service id
                               // ensuite on va additionner les quantites
                               if ($lastbonusInvoice) {
                                   $invoiceCustomer = InvoiceDetails::join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
                                                                       ->where('invoice_id', '>', $lastbonusInvoice->invoice_id)
                                                                       ->where('invoice_details.service_id', $detail['service_id'])
                                                                       ->where('invoices.customer_id', $invoice['customer_id'])
                                                                       //    ->where('invoices.type_facture', 'cash')
                                                                           ->where(function ($query) {
                                                                               $query->where('invoices.type_facture', 'cash')
                                                                                   ->orWhere('invoices.type_facture', 'bonus');
                                                                           })
                                                                       ->whereBetween('invoice_details.created_at',[$date_from_fidelity.' 00:00:00',$date_to_fidelity.' 23:59:59'])
                                                                       ->get();

                               } else {

                                   $invoiceCustomer = InvoiceDetails::join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
                                                                       ->where('invoice_details.service_id', $detail['service_id'])
                                                                           ->where('invoices.customer_id', $invoice['customer_id'])
                                                                           // ->where('invoices.type_facture', 'cash')
                                                                           ->where(function ($query) {
                                                                               $query->where('invoices.type_facture', 'cash')
                                                                                   ->orWhere('invoices.type_facture', 'bonus');
                                                                           })
                                                                       ->whereBetween('invoice_details.created_at',[$date_from_fidelity.' 00:00:00',$date_to_fidelity.' 23:59:59'])
                                                                       ->get();

                               }
                               if($invoiceCustomer->count()>0){
                                   foreach ($invoiceCustomer as $detail_invoice) {
                                       // tester ici s il a paye cash
                                       //faudra tester si price*quantity == total????
                                       $countService += $detail_invoice->quantity;
                                   }

                                   if($countService >= $initvaluefidelity && $initvaluefidelity!=null){
                                       // return "user_id".$request['edited_by_id'];
                                       Bonus::create([
                                           'customer_id'=>$invoice['customer_id'],
                                           'service_id'=>$detail['service_id'],
                                               'amount'=>$detail['price'],
                                               'amount_used'=>0,
                                               'rate'=>0,
                                               'nb_sales'=>$countService,
                                               'enterprise_id'=>$request['enterprise_id'],
                                               'invoice_id'=> $invoice->id,
                                               'user_id'=>$request['edited_by_id']
                                       ]);
                                       $customer->update(['totalbonus'=> $customer->totalbonus +$detail['price']]);
                                      
                                   }
                               }


                           }
                       }


                   }
                   DB::commit();
                   } catch (Exception $th) {

                   DB::rollBack();
                   return "Error ".$th->getMessage();
                                           //throw $th;
                   }

               }
          }

          //if invoice-type=='caution'
          if($invoice['type_facture']=='caution' && $invoice['customer_id']>0){
              //update the customer cautionline
              DB::update('update customer_controllers set totalcautions = totalcautions - ? where id = ?',[$invoice['netToPay'],$invoice['customer_id']]);
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
                      'sync_status'=>'1',
                      'done_at'=>$invoice['date_operation']
                  ]);
              }
          }

          return response()->json([
              'data' =>$this->show($invoice),
              'message'=>'can make invoice'
          ]);
}
public function testwithdrawadjust(){
   // return Carbon::now()->startOfWeek();
   $newwithdraw=  StockHistoryController::create([
        'service_id'=>61,
        'user_id'=>1,
        'invoice_id'=>null,
        'quantity'=>1,
        'price'=>120,
        'type'=>'withdraw',
        'type_approvement'=>'cash',
        'enterprise_id'=>1,
        'motif'=>'vente',
        'done_at'=>Carbon::now(),
        'date_operation'=>Carbon::now(),
        'uuid'=>$this->getUuId('C','ST'),
        'depot_id'=>13,
        'quantity_before'=>496,
    ]);
   return $this->withdrawadjust($newwithdraw->depot_id, $newwithdraw->quantity,$newwithdraw->price, $newwithdraw->id,$newwithdraw->service_id );
}

public function withdrawadjust($depot_id, $quantity_withdraw,$price_withdraw, $operation_withdraw, $service_id){
    $week_start_date = Carbon::now()->startOfWeek();
    $week_end_date = Carbon::now()->endOfWeek();
   
        $method_used=  DepositController::find($depot_id)->withdrawing_method;
        $stockhistories = StockHistoryController::where('service_id', $service_id)
                                            ->where('depot_id', $depot_id)
                                            ->where('type', 'entry')
                                            ->where(function($req){
                                                $req->where('sold','>', 0)->orWhere('sold', null);
                                            });
                                            // ->orWhere('sold', null);
        // Il faudra exclure dans les services les ventes des services qui ont deja expire
        $service_with_expiration_date= $stockhistories->whereBetween('expiration_date',[ $week_start_date, $week_end_date])
                                                        ->orderBy('expiration_date', 'ASC')
                                                        ->get();
        $service_fifo =StockHistoryController::where('service_id', $service_id)
                                            ->where('type', 'entry')
                                            ->where('depot_id', $depot_id)
                                            ->where(function($req){
                                                $req->where('sold','>', 0)->orWhere('sold', null);
                                            })
                                            ->orderBy('id','ASC')
                                            ->get();
        $service_lifo =StockHistoryController::where('service_id', $service_id)
                                            ->where('type', 'entry')
                                            ->where('depot_id', $depot_id)
                                            ->where(function($req){
                                                $req->where('sold','>', 0)->orWhere('sold', null);
                                            })         ->orderBy('id','DESC')
                                            ->get();
        // return  $service_lifo;
        if($service_with_expiration_date->count()){
            $this->Operations($depot_id, $service_with_expiration_date,$price_withdraw, $quantity_withdraw, $operation_withdraw, $service_id );
        }
        else{
            if($method_used=="fifo"){
                $this->Operations($depot_id, $service_fifo,$price_withdraw, $quantity_withdraw, $operation_withdraw, $service_id );
            }
            if($method_used=="lifo"){
                $this->Operations($depot_id, $service_lifo,$price_withdraw, $quantity_withdraw, $operation_withdraw, $service_id );
            }else{
                // method 
            }
        }
}

public function profitCalculations($stockhistory, $operation_withdraw, $quantity_withdraw){
    $quantity_used_array= explode(";", $stockhistory->quantity_used);
    $price_used_array= explode(";", $stockhistory->price_used);
    $index=0;
    $total_buy_prices=0;
    $total_buy_quantity=0;
    forEach($quantity_used_array as $quantity){
        $total_price = floatval($quantity) * floatval($price_used_array[$index]);
        $total_buy_prices +=$total_price;
        $total_buy_quantity +=$quantity;
        $index++;
       // StockHistoryController::find()
    }
    $price_achat_total=$total_buy_quantity*$stockhistory->price;
    $benefice =  $total_buy_prices- $price_achat_total;
    if($stockhistory->price!=0 && $stockhistory->price!=null){
        $stockhistory->profit= $benefice;
    }
    else{
        $stockhistory->profit= 0;
    }
    $stockhistory->save();

    $actualyWithDraw =StockHistoryController::find($operation_withdraw);
    // $priceVenteTotal = $actualyWithDraw->quantity * $actualyWithDraw->price; //ok
    // $priceAchatTotal = $quantity_withdraw * $stockhistory->price;

    // $profit = $priceVenteTotal -$priceAchatTotal;
    if($stockhistory->price!=0 && $stockhistory->price!=null){
        $profit = ($actualyWithDraw->price -  $stockhistory->price) * $quantity_withdraw;
        $actualyWithDraw->profit=$actualyWithDraw->profit+ $profit;
    }else{
        $actualyWithDraw->profit=$actualyWithDraw->profit+0;
    }
    $actualyWithDraw->save();
}
    public function Operations($depot_id,$services,$price_withdraw, $quantity_withdraw, $operation_withdraw, $service_id){
        $isReturn = false;
        forEach($services as $stockhistory){
            
            $diff = $stockhistory->sold != null? $stockhistory->sold-$quantity_withdraw:$stockhistory->quantity -$quantity_withdraw ; // 12000
            if($diff>=0){ //500
                $stockhistory->sold = $diff; //500
                $stockhistory->quantity_used =trim($stockhistory->quantity_used)!=""  && $stockhistory->quantity_used!="0"? $stockhistory->quantity_used . ";".$quantity_withdraw:$quantity_withdraw; //500
                $stockhistory->price_used =trim($stockhistory->price_used)!="" && $stockhistory->price_used!="0" ? $stockhistory->price_used . ";" . $price_withdraw:$price_withdraw; //200
                $stockhistory->operation_used = trim($stockhistory->operation_used) !="" && $stockhistory->operation_used!="0"? $stockhistory->operation_used . ";" . $operation_withdraw: $operation_withdraw;  //613
                
                $stockhistory->save();
                $this->profitCalculations($stockhistory,$operation_withdraw, $quantity_withdraw );
                $isReturn = false;
                break;
            }
            else{
                if($quantity_withdraw<0) break;
                $diff =$stockhistory->sold != null? $quantity_withdraw- $stockhistory->sold: $quantity_withdraw- $stockhistory->quantity;
                $stockhistory->sold= 0;
                $stockhistory->quantity_used =trim($stockhistory->quantity_used)!="" && $stockhistory->quantity_used!="0" ? $stockhistory->quantity_used . ";".$quantity_withdraw-$diff:$quantity_withdraw-$diff;
                $stockhistory->price_used =trim($stockhistory->price_used)!="" && $stockhistory->price_used!="0" ? $stockhistory->price_used . ";" . $price_withdraw:$price_withdraw;
                $stockhistory->operation_used = trim($stockhistory->operation_used) !="" && $stockhistory->operation_used!="0"? $stockhistory->operation_used . ";" . $operation_withdraw: $operation_withdraw;
                $stockhistory->save();
                $this->profitCalculations($stockhistory, $operation_withdraw, $quantity_withdraw-$diff);
                $quantity_withdraw = $diff;
                if($quantity_withdraw>0){
                    $isReturn = true;
                }else{
                    $isReturn = false;
                }
            }
          
        }
       if($isReturn) return $this->withdrawadjust($depot_id, $quantity_withdraw,$price_withdraw, $operation_withdraw, $service_id) ;
    }

    public function saveInvoice(StoreInvoicesRequest $request){
        // return $request;
            $request['uuid']=$this->getUuId("F","C");
            if(!isset($request['date_operation']) && empty($request['date_operation'])){
                $request['date_operation']=date('Y-m-d');
            }

            $invoice=Invoices::create($request->all());
            $ese=$this->getEse($request['edited_by_id']);
            $fidelitymode=$ese['fidelitydefaultmode'];
            $fidelitypointvalue=$ese['fidelitypointvalue'];
            $fidelityinitvalue=$ese['initvaluefidelity'];

           
            //enregistrement des details
            if(isset($request->details)){
            
                foreach ($request->details as $detail) {
                    $detail['invoice_id']=$invoice['id'];
                    $detail['total']=$detail['quantity']*$detail['price'];
                    $detail['point']=ServicesController::find($detail['service_id'])['point'];
                    $newdetail=InvoiceDetails::create($detail);
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
                                'done_at'=>$invoice['date_operation'],
                                'date_operation'=>$invoice['date_operation'],
                                'uuid'=>$this->getUuId('C','ST'),
                                'depot_id'=>$detail['deposit_id'],
                                'quantity_before'=>$stockbefore->available_qte,
                                'total'=>$detail['total'],
                            ]);
                        }
                    }
                    //if detail has subservices(accomp)
                    if(isset($detail['subservices']) && count($detail['subservices'])>0){
                        foreach ($detail['subservices'] as $accomp) {
                            detailinvoicesubservices::create([
                                'service_id'=>$accomp['service_id'],
                                'detail_invoice_id'=>$newdetail['id'],
                                'invoice_id'=>$invoice['id'],
                                'quantity'=>$accomp['quantity'],
                                'price'=>$accomp['price'],
                                'total'=>$accomp['quantity']*$accomp['price'],
                                'note'=>$accomp['note']
                            ]);
                        }
                    }
                }
            }

            if($invoice['type_facture']=='point' &&  $invoice['customer_id']>0){
                $count=$invoice['netToPay'];
                // $count = 0.5;
                $constant=$fidelityinitvalue ;
                $point=$count/$constant ;

                $customer=CustomerController::find($invoice['customer_id']);

                $customerupdated=DB::update('update customer_controllers set totalpoints = totalpoints - ? where id = ?',[$point,$customer['id']]);
            }

            if($fidelitymode=='point' && $invoice['type_facture']=='cash' && $invoice['customer_id']>0){
                $count=$invoice['netToPay'];
                $constant=$fidelityinitvalue;
                if($count>=$constant){
                    
                        $customer=CustomerController::find($invoice['customer_id']);
                        $point=($count/$constant);
                        // number_format();
                        $customerupdated=DB::update('update customer_controllers set totalpoints = totalpoints + ? where id = ?',[$point,$customer['id']]);
                        if($customerupdated){
                             //creating fidelity history ligne
                             customerspointshistory::create([
                                 'customer_id'=>$customer['id'],
                                 'invoice_id'=>$invoice['id'],
                                 'point'=>$point,
                                 'type'=>'point',
                                 'value'=>$ese['fidelitypointvalue']*$point,
                                 'used'=>false,
                                 'done_at'=>$invoice['date_operation']
                             ]);
                        }
                }
            }
            
            if($fidelitymode=='bonus' && $invoice['type_facture']=='cash' && $invoice['customer_id']>0){
              //put the code behind this bonus's logic
            }

            //if invoice-type=='caution'
            if($invoice['type_facture']=='caution' && $invoice['customer_id']>0){
                //update the customer cautionline
                DB::update('update customer_controllers set totalcautions = totalcautions - ? where id = ?',[$invoice['netToPay'],$invoice['customer_id']]);
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
                        'sync_status'=>'1',
                        'done_at'=>$invoice['date_operation']
                    ]);
                }
            }

            return response()->json([
                'data' =>$this->show($invoice),
                'message'=>'can make invoice'
            ]);
    }

    /**
     * store garage data
     */
    public function storegarage(Request $request){

        $User=$this->getinfosuser($request['edited_by_id']);
        $Ese=$this->getEse($request['edited_by_id']);
        if($User && $Ese){
            if($this->isactivatedEse($Ese['id'])){
                return $this->saveInvoiceGarage($request);
            }else{
                //count numbers of invoices done
                $sumInvoices =Invoices::select(DB::raw('count(*) as number'))->where('enterprise_id','=',$Ese['id'])->get('number')->first();
                if ($sumInvoices['number']>=100) {
                    return response()->json([
                        'data' =>'',
                        'message'=>'invoices number exceeded'
                    ]);
                }else{
                    return $this->saveInvoiceGarage($request);
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
     * saving garage invoice
     */

     public function saveInvoiceGarage($request){
        $request['uuid']=$this->getinvoiceUuid($this->getEse($request['edited_by_id'])['id']); 
        $invoice=Invoices::create($request->all());
        $ese=$this->getEse($request['edited_by_id']);
        $fidelitymode=$ese['fidelitydefaultmode'];
        $fidelitypointvalue=$ese['fidelitypointvalue'];
        $fidelityinitvalue=$ese['initvaluefidelity'];

       
        //enregistrement des details
        if(isset($request->details)){
        
            foreach ($request->details as $detail) {
                $detail['invoice_id']=$invoice['id'];
                $detail['total']=$detail['quantity']*$detail['price'];
                $detail['point']=ServicesController::find($detail['service_id'])['point'];
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
                            'done_at'=>$invoice['date_operation'],
                            'date_operation'=>$invoice['date_operation'],
                            'uuid'=>$this->getUuId('C','ST'),
                            'depot_id'=>$detail['deposit_id'],
                            'quantity_before'=>$stockbefore->available_qte,
                        ]);
                    }
                }
            }
        }

       
       
        if($invoice['type_facture']=='point' &&  $invoice['customer_id']>0){
            $count=$invoice['netToPay'];
            $constant=$fidelityinitvalue;
            $point=$count/$constant;

            $customer=CustomerController::find($invoice['customer_id']);

            $customerupdated=DB::update('update customer_controllers set totalpoints = totalpoints - ? where id = ?',[$point,$customer['id']]);
        }

        if($fidelitymode=='point' && $invoice['type_facture']=='cash' && $invoice['customer_id']>0){
            $count=$invoice['netToPay'];
            $constant=$fidelityinitvalue;
            if($count>=$constant){
                
                    $customer=CustomerController::find($invoice['customer_id']);
                    $point=($count/$constant);
                    // number_format();
                    $customerupdated=DB::update('update customer_controllers set totalpoints = totalpoints + ? where id = ?',[$point,$customer['id']]);
                    if($customerupdated){
                         //creating fidelity history ligne
                         customerspointshistory::create([
                             'customer_id'=>$customer['id'],
                             'invoice_id'=>$invoice['id'],
                             'point'=>$point,
                             'type'=>'point',
                             'value'=>$ese['fidelitypointvalue']*$point,
                             'used'=>false,
                         ]);
                    }
            }
        }

        //if invoice-type=='caution'
        if($invoice['type_facture']=='caution' && $invoice['customer_id']>0){
            //update the customer cautionline
            DB::update('update customer_controllers set totalcautions = totalcautions - ? where id = ?',[$invoice['netToPay'],$invoice['customer_id']]);
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

        //If vehicule sent
        if(isset($request['vehicule_id']) && isset($request['customer_id'])){
            //creating licence
            $request['from']=$request['date_operation'];
            $datefromtimestamp=strtotime($request['from']);
            $datefin=date('Y-m-d',strtotime('+'.'6'.'month',$datefromtimestamp));
            $request['to']=$datefin;
            
            licences::create([
                'vehicule_id'=>$request['vehicule_id'],			
                'updated_by'=>$invoice['edited_by_id'],
                'uuid'=>$this->getuuid('C','LCV'),
                'status'=>"available",
                'to'=>$request['to'],
                'from'=>$request['from'],
                'created_by_id'=>$invoice['edited_by_id'],
                'enterprise_id'=>$invoice['enterprise_id']
            ]);
        }

        return response()->json([
            'data' =>$this->show($invoice),
            'message'=>'can make invoice'
        ]);

     }

    /**
     * Saving Offline invoices
     */
    public function storebySafeGuard(Request $request){
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
    public function saveOfflineInvoice(Request $request){
        $invoice= new stdclass;
        if(isset($request['invoice']['customer_uuid']) && $request['invoice']['customer_id']<=0){

            $customer=CustomerController::where('uuid','=',$request['invoice']['customer_uuid'])->get()->first();
            if($customer){
                $input = $request->collect();
               $request= $input->transform(function ($e) use ($customer,$request){
                   $e['invoice']['customer_id']=$customer['id'];
                  return $e;
                });
            } 
        }
        
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
        // else{
        //     $invoice=Invoices::create($request['invoice']);
        //     return response()->json([
        //         'data' =>$this->show($invoice),
        //         'message'=>'can make invoice'
        //     ]);
        // }
        
       
        
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

        $details=collect(InvoiceDetails::leftjoin('moneys as M','invoice_details.money_id','=','M.id')
        ->leftjoin('services_controllers','invoice_details.service_id','=','services_controllers.id')
        ->leftjoin('unit_of_measure_controllers as UOM','services_controllers.uom_id','=','UOM.id')
        ->where('invoice_details.invoice_id','=',$invoices->id)
        ->get(['UOM.name as uom_name','UOM.symbol as uom_symbol','M.money_name','M.abreviation','services_controllers.name as service_name','services_controllers.description','invoice_details.*']));
        $details=$details->transform(function ($detail){
            $detail['subservices']=detailinvoicesubservices::leftjoin('services_controllers','detailinvoicesubservices.service_id','=','services_controllers.id')
            ->leftjoin('unit_of_measure_controllers as UOM','services_controllers.uom_id','=','UOM.id')
            ->where('detailinvoicesubservices.detail_invoice_id','=',$detail->id)
            ->get(['UOM.name as uom_name','UOM.symbol as uom_symbol','services_controllers.name as service_name','services_controllers.description','detailinvoicesubservices.*']);
            return $detail;
        });

        $invoice=Invoices::leftjoin('customer_controllers as C', 'invoices.customer_id','=','C.id')
        ->leftjoin('moneys as M', 'invoices.money_id','=','M.id')
        ->leftjoin('users as U', 'invoices.edited_by_id','=','U.id')
        ->leftjoin('users as collectors','invoices.collector_id','=','collectors.id')
        ->leftjoin('tables as T', 'invoices.table_id','=','T.id')
        ->leftjoin('servants as S', 'invoices.servant_id','=','S.id')
        ->where('invoices.id', '=', $invoices->id)
        ->get(['collectors.full_name as collector_name','collectors.user_name as collector_user_name','T.id as table_id','T.name as table_name','S.id as servant_id','S.name as servant_name','M.abreviation','M.money_name','U.user_name','U.full_name','C.totalpoints','C.totalbonus','C.totalcautions','C.phone','C.mail','C.adress','C.customerName as customer_name','invoices.*'])->first();

        $debt=Debts::join('invoices as I','debts.invoice_id','=','I.id')
        ->leftjoin('moneys as M','I.money_id','=','M.id')
        ->leftjoin('customer_controllers as C','I.customer_id','=','C.id')
        ->where('invoice_id','=',$invoices->id)
        ->get(['M.money_name','M.abreviation','C.phone','C.mail','C.adress','C.customerName','I.uuid as invoiceUuid','I.total as invoice_total_amount','I.amount_paid as invoice_amount_paid','debts.*']);
        if(count($debt)>0){
            $payments=DebtPayments::where('debt_payments.debt_id', '=', $debt[0]['id'])->get();
        }
    
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
