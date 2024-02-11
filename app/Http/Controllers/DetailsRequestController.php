<?php

namespace App\Http\Controllers;

use App\Models\details_request;
use App\Http\Requests\Storedetails_requestRequest;
use App\Http\Requests\Updatedetails_requestRequest;
use Illuminate\Http\Request;

class DetailsRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return details_request::all();
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return details_request::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\details_request  $details_request
     * @return \Illuminate\Http\Response
     */
    public function show(details_request $details_request)
    {
        return details_request::find($details_request);
    }

    public function showforarequest($requestid){

        $details=details_request::where('request_id','=',$requestid)->get();
        return $details;
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\details_request  $details_request
     * @return \Illuminate\Http\Response
     */
    public function edit(details_request $details_request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Updatedetails_requestRequest  $request
     * @param  \App\Models\details_request  $details_request
     * @return \Illuminate\Http\Response
     */
    public function update(Updatedetails_requestRequest $request, details_request $details_request)
    {
        $element = details_request::find($details_request);
        return $element->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\details_request  $details_request
     * @return \Illuminate\Http\Response
     */
    public function destroy(details_request $details_request)
    {
        return details_request::destroy($details_request);
    }

}
