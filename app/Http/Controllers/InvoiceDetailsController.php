<?php

namespace App\Http\Controllers;

use App\Models\InvoiceDetails;
use App\Http\Requests\StoreInvoiceDetailsRequest;
use App\Http\Requests\UpdateInvoiceDetailsRequest;

class InvoiceDetailsController extends Controller
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
     * @param  \App\Http\Requests\StoreInvoiceDetailsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInvoiceDetailsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InvoiceDetails  $invoiceDetails
     * @return \Illuminate\Http\Response
     */
    public function show(InvoiceDetails $invoiceDetails)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InvoiceDetails  $invoiceDetails
     * @return \Illuminate\Http\Response
     */
    public function edit(InvoiceDetails $invoiceDetails)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInvoiceDetailsRequest  $request
     * @param  \App\Models\InvoiceDetails  $invoiceDetails
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInvoiceDetailsRequest $request, InvoiceDetails $invoiceDetails)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InvoiceDetails  $invoiceDetails
     * @return \Illuminate\Http\Response
     */
    public function destroy(InvoiceDetails $invoiceDetails)
    {
        //
    }
}
