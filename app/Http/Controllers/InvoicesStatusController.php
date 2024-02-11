<?php

namespace App\Http\Controllers;

use App\Models\invoicesStatus;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreinvoicesStatusRequest;
use App\Http\Requests\UpdateinvoicesStatusRequest;
use App\Models\DetailsInvoicesStatus;
use App\Models\InvoiceDetails;
use App\Models\Invoices;
use Illuminate\Http\Request;
use stdClass;

class InvoicesStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($invoice)
    {
        return invoicesStatus::join('statuses as ST','invoices_statuses.status_id','=','ST.id')->where('invoice_id','=',$invoice)->get(['invoices_statuses.*','ST.name']);
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
     * @param  \App\Http\Requests\StoreinvoicesStatusRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreinvoicesStatusRequest $request)
    {
        $response=new stdClass;
        //check if it has an old status
        $old=invoicesStatus::where('invoice_id','=',$request['invoice_id'])->get();
        if (count($old)>0) {
            //update the latest
            $latest=invoicesStatus::where('invoice_id','=',$request['invoice_id'])->get()->last();
            if ($latest) {
               
                $request_update= new Request([
                    'invoice_id'=>$latest['invoice_id'],
                    'status_id'=>$latest['status_id'],
                    'from'=>$latest['from'],
                    'enterprise_id'=>$latest['enterprise_id'],
                    'to'=>date('Y-m-d'),
                    'user_id'=>$latest['user_id']
                ]);

                $updated=$latest->update($request_update->all());

                if ($updated) {
                    $request['from']=date('Y-m-d');
                    $request['to']=null;
                    $ese=$this->getEse($request['user_id']);
                    $request['enterprise_id']=$ese['id'];
                    $response=$this->show(invoicesStatus::create($request->all()));
                }
            }
        } else {
            //new collection
            $request['from']=date('Y-m-d');
            $ese=$this->getEse($request['user_id']);
            $request['enterprise_id']=$ese['id'];
            $response=$this->show(invoicesStatus::create($request->all()));
        }

        return $response;
    }

    /**
     * status for a specific invoice
     */
    public function statusForAspecificInvoice($invoice){
        $list=[];
        $old=collect(invoicesStatus::where('invoice_id','=',$invoice)->get());
        $list=$old->map(function ($value){
            return $this->show($value);
        });
        return $list;
    }

    /**
     * Statistic by Invoices
     */
    public function statisticbyinvoices(Request $request){
        $latest=collect(invoicesStatus::join('invoices as I','invoices_statuses.invoice_id','=','I.id')->where('invoices_statuses.status_id','=',$request['status_id'])->get(['I.*']));
        $list=$latest->map( function ($value){
            $invoiceCtrl = new InvoicesController();
            // $invoice=;
            return $invoiceCtrl->ShowInvoicePressing(Invoices::find($value['id']));
        });
        return $list;
    }

     /**
     * Statistic by Details Invoices or Orders
     */
    public function statisticByDetailsInvoices(Request $request){
        $list=[];
        $list=collect(DetailsInvoicesStatus::join('invoice_details as D','details_invoices_statuses.detail_id','=','D.id')->join('services_controllers as S','D.service_id','=','S.id')->where('details_invoices_statuses.status_id','=',$request['status_id'])->get(['S.name as service_name','S.description as service_description','S.photo','D.quantity','D.price','D.total']));
        return $list;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\invoicesStatus  $invoicesStatus
     * @return \Illuminate\Http\Response
     */
    public function show(invoicesStatus $invoicesStatus)
    {
        return invoicesStatus::join('statuses as ST','invoices_statuses.status_id','=','ST.id')->where('invoices_statuses.id','=',$invoicesStatus['id'])->get(['invoices_statuses.*','ST.name'])->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\invoicesStatus  $invoicesStatus
     * @return \Illuminate\Http\Response
     */
    public function edit(invoicesStatus $invoicesStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateinvoicesStatusRequest  $request
     * @param  \App\Models\invoicesStatus  $invoicesStatus
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateinvoicesStatusRequest $request, invoicesStatus $invoicesStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\invoicesStatus  $invoicesStatus
     * @return \Illuminate\Http\Response
     */
    public function destroy(invoicesStatus $invoicesStatus)
    {
        //
    }
}
