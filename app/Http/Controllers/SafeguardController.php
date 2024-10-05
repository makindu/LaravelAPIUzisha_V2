<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerControllerRequest;
use App\Http\Requests\StoreDebtPaymentsRequest;
use App\Http\Requests\StoreDebtsRequest;
use App\Http\Requests\StoreExpendituresRequest;
use App\Http\Requests\StoreInvoicesRequest;
use App\Http\Requests\StoreOtherEntriesRequest;
use App\Models\safeguard;
use App\Http\Requests\StoresafeguardRequest;
use App\Http\Requests\StoreStockHistoryControllerRequest;
use App\Http\Requests\UpdatesafeguardRequest;
use App\Models\CustomerController;
use App\Models\DebtPayments;
use App\Models\Debts;
use App\Models\DepositServices;
use App\Models\detailinvoicesubservices;
use App\Models\InvoiceDetails;
use App\Models\Invoices;
use App\Models\notebooks;
use App\Models\ServicesController;
use App\Models\StockHistoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SafeguardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoresafeguardRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoresafeguardRequest $request)
    {
        // return $request;
        //entries treatment
        $entries=[];
        $entryCtrl = new OtherEntriesController();
        foreach ($request['entries'] as $value) {
            $newrequest= new StoreOtherEntriesRequest($value);
            array_push($entries,$entryCtrl->store($newrequest));
        }

        //expenditures treatment
        $expenditures=[];
        $expenditureCtrl = new ExpendituresController();
        foreach ($request['expenditures'] as $value) {
            $newrequest = new StoreExpendituresRequest($value);
            array_push($expenditures,$expenditureCtrl->store($newrequest));
        }

        //customers treatment
        $customers=[];
        $customerCtrl = new CustomerControllerController();
        foreach ($request['customers'] as $value) {
            $newrequest = new StoreCustomerControllerRequest($value);
            array_push($customers,$customerCtrl->store($newrequest));
        }

        //stock histories treatment
        $stockhistories=[];
        $stockhistoryCtrl =  new StockHistoryControllerController();
        foreach ($request['stockHistories'] as $value) {
            $newrequest = new StoreStockHistoryControllerRequest($value);
            array_push($stockhistories,$stockhistoryCtrl->store($newrequest));
        }

        //invoices treatment
        $invoices=[];
        $invoices=$this->invoicesSafeguard(new Request(["invoices"=>$request['invoices'],"user_id"=>$request['user_id']]))->original;

        //debts treatment
        $debts=[];
        $debtCtrl = new DebtsController();
        foreach ($request['debts'] as $value) {
            $newrequest = new StoreDebtsRequest($value);
            $newrequest['type']='safeguard';
            array_push($debts,$debtCtrl->store($newrequest));
        }

        //payments treatment
        $payments=[];
        $paymentCtrl = new DebtPaymentsController();
        foreach ($request['payments'] as $value) {
            $newrequest = new StoreDebtPaymentsRequest($value);
            $newrequest['type']='safeguard';
            array_push($payments,$paymentCtrl->store($newrequest));
        }

        return ['entries'=>$entries,'expenditures'=>$expenditures,'customers'=>$customers,'stockhistories'=>$stockhistories,'invoices'=>$invoices,'payments'=>$payments,'debts'=>$debts];
    }

    /**
     * new invoices safeguard
     */
    public function invoicesSafeguard(Request $request){
        // return $request;
        $User=$this->getinfosuser($request['user_id']);
        $Ese=$this->getEse($User['id']);
        $message="";

        if($User && $Ese && $this->isactivatedEse($Ese['id']))
        {
            $invoiceCtrl = new InvoicesController();
            $invoices=collect($request['invoices']);
            $data=$invoices->transform(function ($e) use ($invoiceCtrl){
                if(isset($e['invoice']['customer_uuid']) && $e['invoice']['customer_id']<=0){
                    $customer=CustomerController::where('uuid','=',$e['invoice']['customer_uuid'])->get()->first();
                    $e['invoice']['customer_id']=$customer['id'];
                }
    
                    try {
                        $e['invoice']['sync_status']=true;
                        if(!isset($e['invoice']['date_operation']) && empty($e['invoice']['date_operation'])){
                            $e['invoice']['date_operation']=date('Y-m-d');
                        }else{
                            $originalDate = $e['invoice']['date_operation'];
                            $e['invoice']['date_operation'] = date("Y-m-d", strtotime($originalDate));
                        }

                        $invoice=Invoices::create($e['invoice']);
                        //enregistrement des details
                        if(isset($e['details'])){
                        
                            foreach ($e['details'] as $detail) {

                                $detail['invoice_id']=$invoice['id'];
                                $detail['total']=$detail['quantity']*$detail['price'];
                                $type=ServicesController::find($detail['service_id']);
                                if ($type) {
                                    $detail['type_service']=$type['type'];
                                }
                                try {
                                    $detail['sync_status']=true;
                                    $detail['date_operation']=$invoice['date_operation'];
                                    InvoiceDetails::create($detail);
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
                                            'enterprise_id'=>$invoice['enterprise_id'],
                                            'motif'=>'vente',
                                            'done_at'=>$invoice['date_operation'],
                                            'date_operation'=>$invoice['date_operation'],
                                            'uuid'=>$this->getUuId('C','ST'),
                                            'depot_id'=>$detail['deposit_id'],
                                            'quantity_before'=>$stockbefore->available_qte,
                                        ]);
                                    } 
                                    
                                    if(isset($detail['notebooks']) && count($detail['notebooks'])>0){
                                        $actualcustomer=CustomerController::find($e['invoice']['customer_id']);
                                        foreach ($detail['notebooks'] as  $notebook) {
                                            //looking for notebook
                                            $actualnotebook= notebooks::find($notebook);
                                            if($actualnotebook && $actualnotebook->status=='available' && $actualnotebook->user_id==null){
                                                $actualnotebook->update(['user_id'=>$actualcustomer->member_id,'status'=>'unvailable','price'=>$detail['price']]);   
                                            }
                                        }              
                                    }
                                       //if detail has subservices(accomp)
                                        if(isset($detail['subservices']) && count($detail['subservices'])>0){
                                            foreach ($detail['subservices'] as $accomp) {
                                                detailinvoicesubservices::create([
                                                    'service_id'=>$accomp['service_id'],
                                                    'detail_invoice_id'=>$detail['id'],
                                                    'invoice_id'=>$invoice['id'],
                                                    'quantity'=>$accomp['quantity'],
                                                    'price'=>$accomp['price'],
                                                    'total'=>$accomp['quantity']*$accomp['price'],
                                                    'note'=>$accomp['note']
                                                ]);
                                            }
                                        }


                                } catch (\Throwable $th) {
                                    //throw $th;
                                }
                            }
                        }
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
    
                    return $invoiceCtrl->show($invoice);
            });
            $message="success";
            return response()->json([
                'data' =>$data,
                'message'=>$message
            ]); 
        }
        else{
            $message="user unknown";
            return response()->json([
                'data' =>null,
                'message'=>$message
            ]);
        } 
    }
   

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\safeguard  $safeguard
     * @return \Illuminate\Http\Response
     */
    public function show(safeguard $safeguard)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\safeguard  $safeguard
     * @return \Illuminate\Http\Response
     */
    public function edit(safeguard $safeguard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatesafeguardRequest  $request
     * @param  \App\Models\safeguard  $safeguard
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatesafeguardRequest $request, safeguard $safeguard)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\safeguard  $safeguard
     * @return \Illuminate\Http\Response
     */
    public function destroy(safeguard $safeguard)
    {
        //
    }
}
