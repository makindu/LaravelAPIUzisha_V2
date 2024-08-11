<?php

namespace App\Http\Controllers;

use App\Models\attemptactivationaccount;
use App\Http\Requests\StoreattemptactivationaccountRequest;
use App\Http\Requests\UpdateattemptactivationaccountRequest;
use Illuminate\Http\Request;

class AttemptactivationaccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return attemptactivationaccount::all();
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
        return attemptactivationaccount::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\attemptactivationaccount  $attemptactivationaccount
     * @return \Illuminate\Http\Response
     */
    public function show(attemptactivationaccount $attemptactivationaccount)
    {
        return attemptactivationaccount::find($attemptactivationaccount);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\attemptactivationaccount  $attemptactivationaccount
     * @return \Illuminate\Http\Response
     */
    public function edit(attemptactivationaccount $attemptactivationaccount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateattemptactivationaccountRequest  $request
     * @param  \App\Models\attemptactivationaccount  $attemptactivationaccount
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateattemptactivationaccountRequest $request, attemptactivationaccount $attemptactivationaccount)
    {
        $element = attemptactivationaccount::find($attemptactivationaccount);
        return $element->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\attemptactivationaccount  $attemptactivationaccount
     * @return \Illuminate\Http\Response
     */
    public function destroy(attemptactivationaccount $attemptactivationaccount)
    {
        return attemptactivationaccount::destroy($attemptactivationaccount);
    }

}
