<?php

namespace App\Http\Controllers;

use App\Models\Points;
use App\Http\Requests\StorePointsRequest;
use App\Http\Requests\UpdatePointsRequest;

class PointsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseid)
    {
        $list=collect(Points::where('enterprise_id','=',$enterpriseid)->get());
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
     * @param  \App\Http\Requests\StorePointsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePointsRequest $request)
    {
        return $this->show(Points::create($request->all()));
    }

    /**
     * for a specific customer
     */
    public function foracustomer($customerid){
        
        $list=collect(Points::where('customer_id','=',$customerid)->get());
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $listdata;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Points  $points
     * @return \Illuminate\Http\Response
     */
    public function show(Points $points)
    {
        return Points::leftjoin('customer_controllers as C', 'points.customer_id','=','C.id')
        ->leftjoin('enterprises as E', 'points.enterprise_id','=','E.id')
        ->where('points.id', '=', $points->id)
        ->get(['C.customerName','points.*','E.name as enterpriseName'])[0];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Points  $points
     * @return \Illuminate\Http\Response
     */
    public function edit(Points $points)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePointsRequest  $request
     * @param  \App\Models\Points  $points
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePointsRequest $request, Points $points)
    {
        $points->update($request->all());
        return $this->show($points);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Points  $points
     * @return \Illuminate\Http\Response
     */
    public function destroy(Points $points)
    {
        return $points->delete();
    }
}
