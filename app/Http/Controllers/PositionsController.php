<?php

namespace App\Http\Controllers;

use App\Models\positions;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorepositionsRequest;
use App\Http\Requests\UpdatepositionsRequest;

class PositionsController extends Controller
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
     * @param  \App\Http\Requests\StorepositionsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorepositionsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\positions  $positions
     * @return \Illuminate\Http\Response
     */
    public function show(positions $positions)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\positions  $positions
     * @return \Illuminate\Http\Response
     */
    public function edit(positions $positions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatepositionsRequest  $request
     * @param  \App\Models\positions  $positions
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatepositionsRequest $request, positions $positions)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\positions  $positions
     * @return \Illuminate\Http\Response
     */
    public function destroy(positions $positions)
    {
        //
    }
}
