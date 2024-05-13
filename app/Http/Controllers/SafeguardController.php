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
use App\Models\InvoiceDetails;
use App\Models\Invoices;
use Illuminate\Http\Request;

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
        $invoices=$this->invoicesSafeguard(new Request(["invoices"=>$request['invoices'],"user_id"=>$request['user_id']]));

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
            array_push($debts,$paymentCtrl->store($newrequest));
        }

        return ['entries'=>$entries,'expenditures'=>$expenditures,'customers'=>$customers,'stockhistories'=>$stockhistories,'invoices'=>$invoices,'payments'=>$payments,'debts'=>$debts];
    }

    /**
     * new invoices safeguard
     */
    public function invoicesSafeguard(Request $request){
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
                        $e['invoice']['sync_status']="1";
                        $invoice=Invoices::create($e['invoice']);
                        //enregistrement des details
                        if(isset($e['details'])){
                        
                            foreach ($e['details'] as $detail) {
                                $detail['invoice_id']=$invoice['id'];
                                $detail['total']=$detail['quantity']*$detail['price'];
                                try {
                                    $detail['sync_status']=true;
                                    InvoiceDetails::create($detail);
                                    
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
