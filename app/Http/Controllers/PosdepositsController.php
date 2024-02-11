<?php

namespace App\Http\Controllers;

use App\Models\posdeposits;
use App\Http\Requests\StoreposdepositsRequest;
use App\Http\Requests\UpdateposdepositsRequest;

class PosdepositsController extends Controller
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
     * @param  \App\Http\Requests\StoreposdepositsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreposdepositsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\posdeposits  $posdeposits
     * @return \Illuminate\Http\Response
     */
    public function show(posdeposits $posdeposits)
    {
        return posdeposits::join('deposit_controllers as D','posdeposits.deposit_id','=','D.id')->where('posdeposits.id',$posdeposits['id'])->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\posdeposits  $posdeposits
     * @return \Illuminate\Http\Response
     */
    public function edit(posdeposits $posdeposits)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateposdepositsRequest  $request
     * @param  \App\Models\posdeposits  $posdeposits
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateposdepositsRequest $request, posdeposits $posdeposits)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\posdeposits  $posdeposits
     * @return \Illuminate\Http\Response
     */
    public function destroy(posdeposits $posdeposits)
    {
        //
    }
}
