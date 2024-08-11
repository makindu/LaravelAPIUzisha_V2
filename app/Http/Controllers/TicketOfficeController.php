<?php

namespace App\Http\Controllers;

use App\Models\TicketOffice;
use App\Http\Requests\StoreTicketOfficeRequest;
use App\Http\Requests\UpdateTicketOfficeRequest;

class TicketOfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return TicketOffice::all();
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
     * @param  \App\Http\Requests\StoreTicketOfficeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTicketOfficeRequest $request)
    {
       return  TicketOffice::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TicketOffice  $ticketOffice
     * @return \Illuminate\Http\Response
     */
    public function show(TicketOffice $ticketOffice)
    {
       return TicketOffice::find($ticketOffice);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TicketOffice  $ticketOffice
     * @return \Illuminate\Http\Response
     */
    public function edit(TicketOffice $ticketOffice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTicketOfficeRequest  $request
     * @param  \App\Models\TicketOffice  $ticketOffice
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTicketOfficeRequest $request, TicketOffice $ticketOffice)
    {
      return  $ticketOffice->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TicketOffice  $ticketOffice
     * @return \Illuminate\Http\Response
     */
    public function destroy(TicketOffice $ticketOffice)
    {
       return TicketOffice::destroy($ticketOffice);
    }
}
