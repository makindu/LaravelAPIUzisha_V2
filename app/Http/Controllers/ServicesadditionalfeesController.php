<?php

namespace App\Http\Controllers;

use App\Models\servicesadditionalfees;
use App\Http\Requests\StoreservicesadditionalfeesRequest;
use App\Http\Requests\UpdateservicesadditionalfeesRequest;

class ServicesadditionalfeesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseId)
    {
        return servicesadditionalfees::where('enterprise_id','=',$enterpriseId)->get();
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
     * @param  \App\Http\Requests\StoreservicesadditionalfeesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreservicesadditionalfeesRequest $request)
    {
        return servicesadditionalfees::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\servicesadditionalfees  $servicesadditionalfees
     * @return \Illuminate\Http\Response
     */
    public function show(servicesadditionalfees $servicesadditionalfees)
    {
        return servicesadditionalfees::find($servicesadditionalfees);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\servicesadditionalfees  $servicesadditionalfees
     * @return \Illuminate\Http\Response
     */
    public function edit(servicesadditionalfees $servicesadditionalfees)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateservicesadditionalfeesRequest  $request
     * @param  \App\Models\servicesadditionalfees  $servicesadditionalfees
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateservicesadditionalfeesRequest $request, servicesadditionalfees $servicesadditionalfees)
    {
        return $this->show(servicesadditionalfees::find($servicesadditionalfees)->update($request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\servicesadditionalfees  $servicesadditionalfees
     * @return \Illuminate\Http\Response
     */
    public function destroy(servicesadditionalfees $servicesadditionalfees)
    {
        return $servicesadditionalfees->delete();    
    }
}
