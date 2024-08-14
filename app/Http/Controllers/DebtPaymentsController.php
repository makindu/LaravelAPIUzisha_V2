<?php

namespace App\Http\Controllers;

use App\Models\Debts;
use App\Models\Invoices;
use App\Models\DebtPayments;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreDebtPaymentsRequest;
use App\Http\Requests\UpdateDebtPaymentsRequest;
use App\Models\CustomerController;
use Illuminate\Http\Request;
use stdClass;

class DebtPaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list=collect(DebtPayments::all());
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
     * @param  \App\Http\Requests\StoreDebtPaymentsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDebtPaymentsRequest $request)
    {
        if ($request['type']=="safeguard") {
            $newpayment = new stdClass;
            if(!$request['done_at']){
                if (isset($request['created_at'])) {
                    $request['done_at']=$request['created_at'];
                } else {
                    $request['done_at']=date('Y-m-d');
                }
            }

            $debt=Debts::where('uuid','=',$request['debtUuid'])->first();
            $request['debt_id']= $debt['id'];
            $payments=DebtPayments::where('debt_id','=',$debt['id'])
            ->get(); 
            
           

            if(count($payments)>0){
                 $payments=DebtPayments::select(DB::raw('sum(amount_payed) as totalpayed'))
                ->where('debt_id','=',$debt['id'])->first();
                if ($payments['totalpayed']<$debt['amount'] || $payments['totalpayed']==0) {
                    $request['sync_status']=true;
                    $newpayment=DebtPayments::create($request->all());
                    $payments=DebtPayments::select(DB::raw('sum(amount_payed) as totalpayed'))
                    ->where('debt_id','=',$debt['id'])->first();
                    DB::update('update debts set sold = amount - ? where id = ?',[$payments['totalpayed'],$debt['id']]);
                }else{
                    return response()->json([
                        "status"=>400,
                        "data"=>null,
                        "message"=>"already payed"
                    ],400);
                }
            }else{
                $request['sync_status']=true;
                $newpayment=DebtPayments::create($request->all());
                DB::update('update debts set sold = amount - ? where id = ?',[$request['amount_payed'],$debt['id']]);
            }

            

            if ($newpayment) {
                return $this->show($newpayment);
            }else{
                return response()->json([
                    "status"=>400,
                    "data"=>null,
                    "message"=>"incorrect data"
                ],400);
            }
            
        }else{

            if(!$request['done_at']){
                $request['done_at']=date('Y-m-d');
            }
              $debt=Debts::where('id','=',$request['debt_id'])->first();
             
              $payments=DebtPayments::select(DB::raw('sum(amount_payed) as totalpayed'))
              ->where('debt_id','=',$debt['id'])
              ->get()->first();

              if ($payments['totalpayed']<$debt['amount']) {
                $newpayment=DebtPayments::create($request->all());
                 //update the debt
                $payments=DebtPayments::select(DB::raw('sum(amount_payed) as totalpayed'))
                ->where('debt_id','=',$debt['id'])
                ->get()->first();

                DB::update('update debts set sold = amount - ? where id = ?',[$payments['totalpayed'],$debt['id']]);
                return $this->show($newpayment);
            }else{
                return null;
            }
        }
    }

        /**
         * report payments by dates
         */
        public function reportpaymentsbydates(Request $request){
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
               
            $customers=collect(Debts::join('debt_payments as P','debts.id','P.debt_id')
            ->join('customer_controllers as C','debts.customer_id','C.id')
            ->select('C.id',DB::raw('SUM(P.amount_payed) as total_payed'))
            ->where('C.enterprise_id','=',$request['enterprise_id'])
            ->whereBetween('P.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->groupByRaw('C.id')
            ->get()); 

            $customers->map(function($customer) use ($request){
                // $debts=collect(Debts::join('invoices as I','debts.invoice_id','=','I.id')
                // ->where('I.enterprise_id','=',$request['enterprise_id'])
                // ->where('debts.customer_id','=',$customer['customer_id'])
                // ->where('sold','>',0)
                // ->whereBetween('debts.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                // ->get(['debts.*','debts.done_at as created_at','I.uuid','I.netToPay as total_invoice']));

                    $debts=Debts::where('customer_id','=',$customer['id'])
                    ->whereBetween('debts.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                    ->get();
                    
                    $payments=DebtPayments::join('debts as D','debt_payments.debt_id','=','D.id')
                    ->join('users as U','debt_payments.done_by_id','=','U.id')
                    // ->join('invoices as I','D.invoice_id','=','I.id')
                    ->where('D.customer_id','=',$customer['id'])
                    ->whereBetween('debt_payments.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                    ->get(['debt_payments.*','U.user_name as done_by_name']);

                    $infos=CustomerController::where('id','=',$customer['id'])->select('customerName','phone','mail','adress','id as customer_id')->first();
                    $customer['customerName']=$infos['customerName'];
                    $customer['phone']=$infos['phone'];
                    $customer['mail']=$infos['mail'];
                    $customer['adress']=$infos['adress'];
                    $customer['payments']=$payments;
                    $customer['total_payed']=$payments->sum('amount_payed');
                    $customer['total_debts']=$debts->sum('amount');
                    $customer['total_sold']= $debts->sum('sold');
                    return $customer;
            });
         
            return response()->json([
                "data"=>$customers,
                "from"=>$request['from'],
                "to"=>$request['to'],
                "subtotaldebts"=>$customers->sum('total_debts'),
                "subtotalpayments"=>$customers->sum('total_payed'),
                "subtotalsolds"=>$customers->sum('total_sold'),
                "money"=>$this->defaultmoney($request['enterprise_id'])
            ]);
        }

      /**
      * report payments by customers
      */
      public function paymentsbycutomersbasedondate(Request $request){
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
            $customers=collect(CustomerController::whereIn('id',$request['customers'])->select('id','customerName','phone','mail','adress')->get());
            $customers->transform(function ($customer) use ($request){
                $debts=Debts::where('customer_id','=',$customer['id'])
                ->get();
                
                $payments=DebtPayments::join('debts as D','debt_payments.debt_id','=','D.id')
                ->join('users as U','debt_payments.done_by_id','=','U.id')
                ->where('D.customer_id','=',$customer['id'])
                ->whereBetween('debt_payments.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->get(['debt_payments.*','U.user_name as done_by_name']);

                $customer['total_payed']=$payments->sum('amount_payed');
                $customer['total_debts']=$debts->sum('amount');
                $customer['total_sold']=$debts->sum('sold');
                $customer['payments']=$payments;
                return $customer;
            });
        }
        return response()->json([
            "data"=>$customers,
            "from"=>$request['from'],
            "to"=>$request['to'],
            "subtotaldebts"=>$customers->sum('total_debts'),
            "subtotalpayments"=>$customers->sum('total_payed'),
            "subtotalsolds"=>$customers->sum('total_sold'),
            "money"=>$this->defaultmoney($request['enterprise_id'])
        ]);
      }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DebtPayments  $debtPayments
     * @return \Illuminate\Http\Response
     */
    public function show(DebtPayments $debtPayments)
    {
        return DebtPayments::leftjoin('debts as D','debt_payments.debt_id','=','D.id')
        ->leftjoin('invoices as I','D.invoice_id','=','I.id')
        ->leftjoin('customer_controllers as C','I.customer_id','=','C.id')
        ->leftjoin('users as U','debt_payments.done_by_id','=','U.id')
        ->where('debt_payments.id','=',$debtPayments['id'])->get(['debt_payments.*','C.customerName','C.id as customerId','I.id as invoiceId','U.user_name as done_by_name'])[0];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DebtPayments  $debtPayments
     * @return \Illuminate\Http\Response
     */
    public function edit(DebtPayments $debtPayments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDebtPaymentsRequest  $request
     * @param  \App\Models\DebtPayments  $debtPayments
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDebtPaymentsRequest $request, DebtPayments $debtPayments)
    {
        return $debtPayments->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DebtPayments  $debtPayments
     * @return \Illuminate\Http\Response
     */
    public function destroy(DebtPayments $debtPayments)
    {
        return DebtPayments::destroy($debtPayments);
    }
}
