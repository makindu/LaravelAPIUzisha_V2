<?php

namespace App\Http\Controllers;

use App\Models\enterprisesettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreenterprisesettingsRequest;
use App\Http\Requests\UpdateenterprisesettingsRequest;

class EnterprisesettingsController extends Controller
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
     * @param  \App\Http\Requests\StoreenterprisesettingsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreenterprisesettingsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\enterprisesettings  $enterprisesettings
     * @return \Illuminate\Http\Response
     */
    public function show(enterprisesettings $enterprisesettings)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\enterprisesettings  $enterprisesettings
     * @return \Illuminate\Http\Response
     */
    public function edit(enterprisesettings $enterprisesettings)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateenterprisesettingsRequest  $request
     * @param  \App\Models\enterprisesettings  $enterprisesettings
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateenterprisesettingsRequest $request, enterprisesettings $enterprisesettings)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\enterprisesettings  $enterprisesettings
     * @return \Illuminate\Http\Response
     */
    public function destroy(enterprisesettings $enterprisesettings)
    {
        //
    }
}
