<?php

namespace App\Http\Controllers;

use App\Models\licences;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorelicencesRequest;
use App\Http\Requests\UpdatelicencesRequest;

class LicencesController extends Controller
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
     * @param  \App\Http\Requests\StorelicencesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorelicencesRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\licences  $licences
     * @return \Illuminate\Http\Response
     */
    public function show(licences $licences)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\licences  $licences
     * @return \Illuminate\Http\Response
     */
    public function edit(licences $licences)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatelicencesRequest  $request
     * @param  \App\Models\licences  $licences
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatelicencesRequest $request, licences $licences)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\licences  $licences
     * @return \Illuminate\Http\Response
     */
    public function destroy(licences $licences)
    {
        //
    }
}
