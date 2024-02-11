<?php

namespace App\Http\Controllers;

use App\Models\DebtPayments;
use App\Http\Requests\StoreDebtPaymentsRequest;
use App\Http\Requests\UpdateDebtPaymentsRequest;
use App\Models\Debts;
use App\Models\Invoices;

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
            $debt=Debts::where('uuid','=',$request['debtUuid'])->first();
            $request['debt_id']= $debt['id'];
            DebtPayments::create($request->all());
        }else{
            return $this->show(DebtPayments::create($request->all()));
        }
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
