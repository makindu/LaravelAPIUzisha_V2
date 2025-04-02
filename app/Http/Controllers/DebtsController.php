<?php

namespace App\Http\Controllers;

use App\Models\Debts;
use App\Models\DebtPayments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreDebtsRequest;
use App\Http\Requests\UpdateDebtsRequest;
use App\Models\CustomerController;
use App\Models\InvoiceDetails;
use App\Models\Invoices;
use stdClass;

class DebtsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise_id)
    {
        $list=collect(Debts::join('invoices as I','debts.invoice_id','=','I.id')->where('I.type_facture','=','credit')->where('I.enterprise_id','=',$enterprise_id)->where('debts.status','=','0')->where('debts.sold','>',0)->get(['debts.*']));
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $listdata;
    }

    /**
     * searching of debts by done paginated
     */
    public function searchdebtsbydoneby(Request $request){
        $searchTerm = $request->query('keyword', '');
        $enterpriseId = $request->query('enterprise_id', 0);  
        $actualuser=$this->getinfosuser($request->query('user_id'));
        if ($actualuser['user_type']=='super_admin') {
            
            $list =Debts::Join('invoices', 'debts.invoice_id', '=', 'invoices.id')
                ->join('customer_controllers','invoices.customer_id','=','customer_controllers.id')
                ->where('invoices.enterprise_id', '=', $enterpriseId)
                ->where(function($query) use ($searchTerm) {
                    $query->where('debts.amount', 'LIKE', "%$searchTerm%")
                    ->orWhere('debts.sold', 'LIKE', "%$searchTerm%")
                    ->orWhere('debts.uuid', 'LIKE', "%$searchTerm%")
                    ->orWhere('debts.done_at', 'LIKE', "%$searchTerm%")
                    ->orWhere('debts.id', 'LIKE', "%$searchTerm%")
                    ->orWhere('customer_controllers.customerName', 'LIKE', "%$searchTerm%")
                    ->orWhere('customer_controllers.phone', 'LIKE', "%$searchTerm%")
                    ->orWhere('customer_controllers.mail', 'LIKE', "%$searchTerm%")
                    ->orWhere('customer_controllers.uuid', 'LIKE', "%$searchTerm%");
                })
                ->select('debts.*')
                ->paginate(10)
                ->appends($request->query());

            $list->getCollection()->transform(function ($item){
                return $this->show($item);
            });
            return $list;

        } else {
            
            $list =Debts::Join('invoices', 'debts.invoice_id', '=', 'invoices.id')
            ->join('customer_controllers','invoices.customer_id','=','customer_controllers.id')
            ->where('debts.created_by_id', '=', $actualuser['id'])
            ->where(function($query) use ($searchTerm) {
                $query->where('debts.amount', 'LIKE', "%$searchTerm%")
                ->orWhere('debts.sold', 'LIKE', "%$searchTerm%")
                ->orWhere('debts.uuid', 'LIKE', "%$searchTerm%")
                ->orWhere('debts.done_at', 'LIKE', "%$searchTerm%")
                ->orWhere('debts.id', 'LIKE', "%$searchTerm%")
                ->orWhere('customer_controllers.customerName', 'LIKE', "%$searchTerm%")
                ->orWhere('customer_controllers.phone', 'LIKE', "%$searchTerm%")
                ->orWhere('customer_controllers.mail', 'LIKE', "%$searchTerm%")
                ->orWhere('customer_controllers.uuid', 'LIKE', "%$searchTerm%");
            })
            ->select('debts.*')
            ->paginate(10)
            ->appends($request->query());

        $list->getCollection()->transform(function ($item){
            return $this->show($item);
        });
        return $list;
        }
    }

    /**
     * looking debt by invoice uui or id
     */
    public function searchingbyidoruuid(Request $request){

        $list=collect(Debts::join('invoices as I','debts.invoice_id','=','I.id')
                ->where('I.type_facture','=','credit')
                ->where('I.enterprise_id','=',$request['enterprise_id'])
                ->where('I.id','=',$request['keyword'])
                ->orWhere('I.uuid','=',$request['keyword'])
                ->orWhere('debts.uuid','=',$request['keyword'])
                ->get(['debts.*']));
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $listdata;
    }

     /**
      * report credits by customers
      */
      public function creditsByCutomers(Request $request){
        $customers=[];
        if(isset($request['from']) && empty($request['to'])){
            $request['to']=$request['from'];
        } 
        
        if(empty($request['from']) && isset($request['to'])){
            $request['from']=$request['to'];
        }
        
        if(empty($request['from']) && empty($request['to'])){
            $request['from']=date('Y-m-d');
            $request['to']=date('Y-m-d');
        }
           
        if(isset($request['customers']) && !empty($request['customers'])){
            $customers=collect(CustomerController::whereIn('id',$request['customers'])->get());
            $customers->transform(function ($customer) use ($request){
                $total=Debts::join('invoices as I','debts.invoice_id','=','I.id')
                ->select(DB::raw('SUM(debts.sold) as total'))
                ->where('debts.customer_id','=',$customer['id'])
                ->where('debts.sold','>',0)
                ->whereBetween('debts.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->get('total')->first();
                $customer['total']=$total['total'];
                //debts list
                     $debts=collect(Debts::join('invoices as I','debts.invoice_id','=','I.id')
                            ->where('debts.customer_id','=',$customer['id'])
                            ->where('sold','>',0)
                            ->whereBetween('debts.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                            ->get(['debts.*','I.uuid','I.netToPay as total_invoice']));

                        $debts->transform(function ($debt){
                            $details=DB::table('invoice_details')
                            ->leftjoin('services_controllers as S','invoice_details.service_id','=','S.id')
                            ->leftjoin('unit_of_measure_controllers as UOM','S.uom_id','=','UOM.id')
                            ->where('invoice_details.invoice_id','=',$debt['invoice_id'])
                            ->select('invoice_details.service_id','S.name','UOM.symbol','invoice_details.quantity','invoice_details.total')
                            ->get();
                            $debt['details']=$details;
                            $debt['already_payed']=DebtPayments::where('debt_id','=',$debt['id'])->get()->sum('amount_payed');
                            return $debt;
                        });
                    $customer['debts']=$debts;
                    // $customer['totalgeneralpayed']=$debts->sum('already_payed');
                return $customer;
            });

            
        }
        return response()->json([
            "data"=>$customers,
            "from"=>$request['from'],
            "to"=>$request['to'],
            "total_general"=>$customers->sum('total'),
            "money"=>$this->defaultmoney($request['enterprise_id'])
        ]);
      } 
      
      /**
      * report credits by customers
      */
      public function creditsByCutomersbasedondate(Request $request){
        $customers=[];
        if(isset($request['from']) && empty($request['to'])){
            $request['to']=$request['from'];
        } 
        
        if(empty($request['from']) && isset($request['to'])){
            $request['from']=$request['to'];
        }
        
        if(empty($request['from']) && empty($request['to'])){
            $request['from']=date('Y-m-d');
            $request['to']=date('Y-m-d');
        }
           
        if(isset($request['customers']) && !empty($request['customers'])){
            $customers=collect(CustomerController::whereIn('id',$request['customers'])->get());
            $customers->transform(function ($customer) use ($request){ 
                //debts list
                $customer['soldnet']=Debts::select(DB::raw('SUM(sold) as soldnet'))
                ->where('customer_id','=',$customer['id'])->first()->soldnet;

                $totaldebts=Debts::where('debts.customer_id','=',$customer['id'])
                ->whereBetween('debts.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->select(DB::raw('SUM(debts.amount) as totaldebts'))
                ->first(); 

                $totalpayed=DebtPayments::join('debts as D','debt_payments.debt_id','=','D.id')
                ->where('D.customer_id','=',$customer['id'])
                ->whereBetween('debt_payments.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->select(DB::raw('SUM(debt_payments.amount_payed) as totalpayed'))
                ->first();

                     $debts=collect(Debts::join('invoices as I','debts.invoice_id','=','I.id')
                            ->where('debts.customer_id','=',$customer['id'])
                            // ->where('sold','>',0)
                            ->whereBetween('debts.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                            ->get(['debts.*','I.uuid','I.netToPay as total_invoice']));

                        $debts->transform(function ($debt) use ($request){
                            $details=InvoiceDetails::leftjoin('services_controllers as S','invoice_details.service_id','=','S.id')
                            ->leftjoin('unit_of_measure_controllers as UOM','S.uom_id','=','UOM.id')
                            ->where('invoice_details.invoice_id','=',$debt['invoice_id'])
                            ->select('invoice_details.service_id','S.name','UOM.symbol','invoice_details.quantity','invoice_details.total')
                            ->get();
                            $debt['details']=$details;
                            $debt['already_payed']=DebtPayments::where('debt_id','=',$debt['id'])
                            ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                            ->get()->sum('amount_payed');
                            return $debt;
                        });
                    $customer['debts']=$debts;
                    $customer['total']=$totaldebts->totaldebts;
                    $customer['totalpayed']=$totalpayed->totalpayed;
                    $customer['sold']= $customer['total'] - $customer['totalpayed'];
                return $customer;
            });

            
        }
        return response()->json([
            "data"=>$customers,
            "from"=>$request['from'],
            "to"=>$request['to'],
            "total_general"=>$customers->sum('total'),
            "totalgeneralpayed"=>$customers->sum('totalpayed'),
            "totalgeneralsold"=>$customers->sum('sold'),
            "totalgeneralsoldnet"=>$customers->sum('soldnet'),
            "money"=>$this->defaultmoney($request['enterprise_id'])
        ]);
      }
   
    /**
     * get list of debts grouped by customer
     */
    public function debtsgroupedbycustomer(Request $request){
        
        if(isset($request['from']) && isset($request['to'])==false){
            $request['to']=$request['from'];
        } 
        
        if(isset($request['from'])==false && isset($request['to'])){
            $request['from']=$request['to'];
        }
        
        if(isset($request['from'])==false && isset($request['to'])==false){
            $request['from']=date('Y-m-d');
            $request['to']=date('Y-m-d');
        }
           
    
        $list=collect(Debts::join('invoices as I','debts.invoice_id','=','I.id')
        ->select('debts.customer_id', DB::raw('SUM(debts.sold) as total'))
        ->where('I.type_facture','=','credit')
        ->where('I.enterprise_id','=',$request['enterprise_id'])
        ->where('debts.sold','>',0)
        ->whereBetween('debts.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
        ->groupByRaw('debts.customer_id')
        ->get()); 
        $listdata=$list->transform(function ($item) use ($request){
            $item['customer']=CustomerController::where('id','=',$item['customer_id'])->select('customerName','adress','phone','mail')->first();
            $debts=collect(Debts::join('invoices as I','debts.invoice_id','=','I.id')
            ->where('debts.customer_id','=',$item['customer_id'])
            ->where('sold','>',0)
            ->whereBetween('debts.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->get(['debts.*','I.uuid','I.netToPay as total_invoice']));
            $item['debts']=$debts->transform(function ($debt){
                $details=DB::table('invoice_details')
                ->leftjoin('services_controllers as S','invoice_details.service_id','=','S.id')
                ->leftjoin('unit_of_measure_controllers as UOM','S.uom_id','=','UOM.id')
                ->where('invoice_details.invoice_id','=',$debt['invoice_id'])
                ->select('invoice_details.service_id','S.name','UOM.symbol','invoice_details.quantity','invoice_details.total')
                ->get();
                $debt['details']=$details;
                $debt['already_payed']=DebtPayments::where('debt_id','=',$debt['id'])->get()->sum('amount_payed');
                return $debt;
            });
            return $item;
        });

        return response()->json([
            "data"=>$listdata,
            "from"=>$request['from'],
            "to"=>$request['to'],
            "money"=>$this->defaultmoney($request['enterprise_id'])
        ]);
    }

    /**
     * get list of debts grouped by customer
     */
    public function debtsgroupedbycustomerbasedodateoperation(Request $request){
        
        if(isset($request['from']) && isset($request['to'])==false){
            $request['to']=$request['from'];
        } 
        
        if(isset($request['from'])==false && isset($request['to'])){
            $request['from']=$request['to'];
        }
        
        if(isset($request['from'])==false && isset($request['to'])==false){
            $request['from']=date('Y-m-d');
            $request['to']=date('Y-m-d');
        }
           
    
        $list=collect(Debts::join('customer_controllers as C','debts.customer_id','C.id')
        ->select('debts.customer_id')
        ->where('C.enterprise_id','=',$request['enterprise_id'])
        ->whereBetween('debts.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
        ->groupByRaw('debts.customer_id')
        ->get()); 
        // return $list;
        $listdata=$list->transform(function ($item) use ($request){
            $item['customer']=CustomerController::select('customerName','adress','phone','mail','id as customer_id')
            ->find($item['customer_id']);
            if (!$item['customer']) {
                $item['customer']= new stdClass;
                $item['customer']->customerName="Client anonyme";
                $item['customer']->adress="aucune adresse";
                $item['customer']->phone="aucune numero";
                $item['customer']->mail="aucune adresse mail";
            }

            $item['soldnet']=Debts::select(DB::raw('SUM(sold) as soldnet'))
            ->where('customer_id','=',$item['customer_id'])->first()->soldnet;

            $debts=collect(Debts::join('invoices as I','debts.invoice_id','=','I.id')
            ->where('debts.customer_id','=',$item['customer_id'])
            ->whereBetween('debts.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            // ->whereBetween(DB::raw('DATE(debts.created_at)'),[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->get(['debts.*','debts.done_at as created_at','I.uuid','I.netToPay as total_invoice'])); 
            
            $totaldebts=Debts::where('debts.customer_id','=',$item['customer_id'])
            ->whereBetween('debts.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->select(DB::raw('SUM(debts.amount) as totaldebts'))
            ->first(); 
            
            $totalpayed=DebtPayments::join('debts as D','debt_payments.debt_id','=','D.id')
            ->where('D.customer_id','=',$item['customer_id'])
            ->whereBetween('debt_payments.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->select(DB::raw('SUM(debt_payments.amount_payed) as totalpayed'))
            ->first();
            // return $totalpayed;

            $item['debts']=$debts->transform(function ($debt) use ($request){
                $details=DB::table('invoice_details')
                ->join('services_controllers as S','invoice_details.service_id','=','S.id')
                ->leftjoin('unit_of_measure_controllers as UOM','S.uom_id','=','UOM.id')
                ->where('invoice_details.invoice_id','=',$debt['invoice_id'])
                ->select('invoice_details.service_id','S.name','UOM.symbol','invoice_details.quantity','invoice_details.total')
                ->get();
                $debt['details']=$details;
                $debt['already_payed']=DebtPayments::where('debt_id','=',$debt['id'])
                ->whereBetween('debt_payments.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->get()->sum('amount_payed');
                return $debt;
            });
            // $item['debts']=$debts;
            $item['total']=$totaldebts->totaldebts;
            $item['totalpayed']=$totalpayed->totalpayed;
            $item['sold']= $item['total'] - $item['totalpayed'];
          
            return $item;
        });

        return response()->json([
            "data"=>$listdata,
            "from"=>$request['from'],
            "totalgeneralcredit"=>$listdata->sum('total'),
            "totalgeneralpayed"=>$listdata->sum('totalpayed'),
            "totalgeneralsold"=>$listdata->sum('sold'),
            "totalgeneralsoldnet"=>$listdata->sum('soldnet'),
            "to"=>$request['to'],
            "money"=>$this->defaultmoney($request['enterprise_id'])
        ]);
    }
    
    /**
     * get list of debts grouped by customer filtered by criteria
     */
    public function debtsfilteredbycriteria(Request $request){

        if(isset($request['from']) && empty($request['to'])){
            $request['to']=$request['from'];
        } 
        
        if(empty($request['from']) && isset($request['to'])){
            $request['from']=$request['to'];
        }
        
        if(empty($request['from']) && empty($request['to'])){
            $request['from']=date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        switch ($request['criteria']) {
            case 'payed':
                
                $list=collect(Debts::join('invoices as I','debts.invoice_id','=','I.id')
                ->select('debts.customer_id', DB::raw('SUM(debts.sold) as total'))
                ->where('I.type_facture','=','credit')
                ->where('I.enterprise_id','=',$request['enterprise_id'])
                ->where('debts.sold','<=',0)
                ->whereBetween('debts.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->groupByRaw('debts.customer_id')
                ->get());
                break;
            case 'partially':
                
                    $list=collect(Debts::join('invoices as I','debts.invoice_id','=','I.id')
                    ->select('debts.customer_id', DB::raw('SUM(debts.sold) as total'))
                    ->where('I.type_facture','=','credit')
                    ->where('I.enterprise_id','=',$request['enterprise_id'])
                    ->where('debts.sold','>',0)
                    ->whereBetween('debts.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                    ->groupByRaw('debts.customer_id')
                    ->get());
                break;
            case "not_payed" :
                    $list=collect(Debts::join('invoices as I','debts.invoice_id','=','I.id')
                    ->select('debts.customer_id', DB::raw('SUM(debts.sold) as total'))
                    ->where('I.type_facture','=','credit')
                    ->where('I.enterprise_id','=',$request['enterprise_id'])
                    ->where('debts.sold','=','debts.amount')
                    ->whereBetween('debts.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                    ->groupByRaw('debts.customer_id')
                    ->get());
                break;
            default:
                $list=[];
                break;
        }           

        $listdata=$list->transform(function ($item) use ($request){
            $item['customer']=CustomerController::where('id','=',$item['customer_id'])->select('customerName','adress','phone','mail')->first();
            $debts=collect(Debts::join('invoices as I','debts.invoice_id','=','I.id')
            ->where('debts.customer_id','=',$item['customer_id'])
            ->where('sold','>',0)
            ->whereBetween('debts.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->get(['debts.*','I.uuid','I.netToPay as total_invoice']));
            $item['debts']=$debts->transform(function ($debt){
                $details=DB::table('invoice_details')
                ->leftjoin('services_controllers as S','invoice_details.service_id','=','S.id')
                ->leftjoin('unit_of_measure_controllers as UOM','S.uom_id','=','UOM.id')
                ->where('invoice_details.invoice_id','=',$debt['invoice_id'])
                ->select('invoice_details.service_id','S.name','UOM.symbol','invoice_details.quantity','invoice_details.total')
                ->get();
                $debt['details']=$details;
                $debt['already_payed']=DebtPayments::where('debt_id','=',$debt['id'])->get()->sum('amount_payed');
                return $debt;
            });
            return $item;
        });

        return response()->json([
            "data"=>$listdata,
            "from"=>$request['from'],
            "to"=>$request['to'],
            "money"=>$this->defaultmoney($request['enterprise_id'])
        ]);
    }

    /**
     * Compte courant Customer
     */
    public function compteCourant(Request $request){
        $list=collect(Debts::join('invoices as I','debts.invoice_id','=','I.id')->where('debts.customer_id','=',$request['customer_id'])->where('debts.status','=','0')->get(['debts.*']));
        $listdata=$list->map(function ($item){
            return $this->show($item);
        });
        return $listdata;
    }  
    
    /**
     * Compte courant Customer
     */
    public function FilteredcompteCourant(Request $request){

        if (empty($request['from']) && empty($request['to'])) {
            $request['from']=date('Y-m-d');
            $request['to']=date('Y-m-d');
        } 

        $list=collect(Debts::join('invoices as I','debts.invoice_id','=','I.id')->whereBetween('debts.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('debts.customer_id','=',$request['customer_id'])->where('debts.status','=','0')->get(['debts.*']));
        $listdata=$list->map(function ($item){
            return $this->show($item);
        });
        return ["debts"=>$listdata,"from"=> $request['from'],"to"=> $request['to']];
    }

    /**
     * get payments for a debts
     */
    public function getPayments(Request $request){
        return DebtPayments::leftjoin('users as U', 'debt_payments.done_by_id','=','U.id')
        ->where('debt_payments.debt_id', '=', $request['debt_id'])
        ->get(['U.user_name','debt_payments.*']);
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
     * @param  \App\Http\Requests\StoreDebtsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDebtsRequest $request)
    {
        //safeguard
        if ($request['type']=='safeguard') {
            $invoice=Invoices::where('uuid','=',$request['debt']['invoiceUuid'])->first();
            if ($invoice) {
                $request['invoice_id']= $invoice['id'];
                $request['customer_id']= $invoice['customer_id'];
                $request['created_by_id']=$request['debt']['created_by_id'];
                $request['amount']=$request['debt']['amount'];
                $request['sold']=$request['debt']['sold'];
                $request['uuid']=$request['debt']['uuid'];
                $request['sync_status']=true;
                $request['status']='0';
               return  Debts::create($request->all());
            }
            return null;
            
        }else{
           return Debts::create($request->all());   
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Debts  $debts
     * @return \Illuminate\Http\Response
     */
    public function show(Debts $debts)
    {
        $debt=Debts::join('invoices as I','debts.invoice_id','=','I.id')
        ->leftjoin('moneys as M','I.money_id','=','M.id')
        ->leftjoin('customer_controllers as C','I.customer_id','=','C.id')
        ->where('debts.id','=',$debts->id)
        ->get(['M.money_name','M.abreviation','C.customerName','I.uuid as invoiceUuid','I.total as invoice_total_amount','I.amount_paid as invoice_amount_paid','debts.*'])[0];

        $payments=DebtPayments::leftjoin('users as U', 'debt_payments.done_by_id','=','U.id')
        ->where('debt_payments.debt_id', '=', $debt['id'])
        ->get(['U.user_name','debt_payments.*']);

        return ['debt'=>$debt,'payments'=>$payments];
    }

    /**
     * Payment
     */
    public function payment_debt(Request $request){
        $message='';
        $debt=Debts::where('id','=',$request['debt_id'])->get()[0];
        if($debt){
            if($debt['sold']>0 && $debt['status']=='0' && $request['amount_payed']<=$debt['sold']){
                $request['uuid']=$this->getUuId('P','D');
                $request['sync_status']='1';
                DebtPayments::create($request->all());
                $sumpayments=0;
                $allpayments=DebtPayments::where('debt_id','=',$debt['id'])->get();
                foreach ($allpayments as $key => $payment) {
                    $sumpayments=$sumpayments+$payment['amount_payed'];
                }

                if($sumpayments==$debt['amount']){
                    //update debt
                    DB::update('update debts set sold = ?, status= ? where id = ? ',[$debt['amount']-$sumpayments,'1',$debt['id']]);
                }else{
                    //update debt
                    DB::update('update debts set sold = ? where id = ? ',[$debt['amount']-$sumpayments,$debt['id']]);
                }
                
                $message='success';
                $debt=Debts::where('id','=',$request['debt_id'])->get()[0];
            }else{
                $message='finished';
            }
        }

        return response()->json([
            'data' =>$this->show($debt),
            'message'=>$message
        ]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Debts  $debts
     * @return \Illuminate\Http\Response
     */
    public function edit(Debts $debts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDebtsRequest  $request
     * @param  \App\Models\Debts  $debts
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDebtsRequest $request, Debts $debts)
    {
        return $this->show(Debts::find($debts->update($request->all())));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Debts  $debts
     * @return \Illuminate\Http\Response
     */
    public function destroy(Debts $debts)
    {
        DebtPayments::where('debt_payments.debt_id', '=', $debts->id)->delete();
        
         return Debts::destroy($debts);
    }
}
