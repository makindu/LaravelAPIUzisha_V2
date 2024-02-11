<?php

namespace App\Http\Controllers;

use App\Models\Invoices;
use App\Models\servants;
use Illuminate\Http\Request;
use App\Http\Requests\StoreservantsRequest;
use App\Http\Requests\UpdateservantsRequest;

class ServantsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise_id)
    {
        return servants::where('enterprise_id','=',$enterprise_id)->get();
    }

    public function getsales($servant){
        
        return Invoices::where('servant_id','=',$servant)
        ->get('invoices.*');
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
     * @param  \App\Http\Requests\StoreservantsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreservantsRequest $request)
    {
        return servants::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\servants  $servants
     * @return \Illuminate\Http\Response
     */
    public function show(servants $servants)
    {
        return servants::find($servants->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\servants  $servants
     * @return \Illuminate\Http\Response
     */
    public function edit(servants $servants)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateservantsRequest  $request
     * @param  \App\Models\servants  $servants
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateservantsRequest $request, servants $servants)
    {
         $servants->update($request->all());
        return servants::find($servants->id);
    }

    public function update2(Request $request,$id){
        $serv=servants::find($id);
        $serv->update($request->all());

        return servants::find($id);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\servants  $servants
     * @return \Illuminate\Http\Response
     */
    public function destroy(servants $servants)
    {
       return $servants->delete();
    }

    public function delete($servant){
        $find=servants::find($servant);
        return $find->delete();
    }
}
