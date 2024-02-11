<?php

namespace App\Http\Controllers;

use App\Models\owners;
use App\Http\Requests\StoreownersRequest;
use App\Http\Requests\UpdateownersRequest;

class OwnersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return owners::all();
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
     * @param  \App\Http\Requests\StoreownersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreownersRequest $request)
    {
        return owners::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\owners  $owners
     * @return \Illuminate\Http\Response
     */
    public function show(owners $owners)
    {
        return owners::find($owners);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\owners  $owners
     * @return \Illuminate\Http\Response
     */
    public function edit(owners $owners)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateownersRequest  $request
     * @param  \App\Models\owners  $owners
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateownersRequest $request, owners $owners)
    {
        return $owners->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\owners  $owners
     * @return \Illuminate\Http\Response
     */
    public function destroy(owners $owners)
    {
        //
    }
}
