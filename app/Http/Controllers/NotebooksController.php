<?php

namespace App\Http\Controllers;

use App\Models\notebooks;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorenotebooksRequest;
use App\Http\Requests\UpdatenotebooksRequest;

class NotebooksController extends Controller
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
     * @param  \App\Http\Requests\StorenotebooksRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorenotebooksRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\notebooks  $notebooks
     * @return \Illuminate\Http\Response
     */
    public function show(notebooks $notebooks)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\notebooks  $notebooks
     * @return \Illuminate\Http\Response
     */
    public function edit(notebooks $notebooks)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatenotebooksRequest  $request
     * @param  \App\Models\notebooks  $notebooks
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatenotebooksRequest $request, notebooks $notebooks)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\notebooks  $notebooks
     * @return \Illuminate\Http\Response
     */
    public function destroy(notebooks $notebooks)
    {
        //
    }
}
