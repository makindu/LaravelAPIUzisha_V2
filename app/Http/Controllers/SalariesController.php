<?php

namespace App\Http\Controllers;

use App\Models\salaries;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoresalariesRequest;
use App\Http\Requests\UpdatesalariesRequest;

class SalariesController extends Controller
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
     * @param  \App\Http\Requests\StoresalariesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoresalariesRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\salaries  $salaries
     * @return \Illuminate\Http\Response
     */
    public function show(salaries $salaries)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\salaries  $salaries
     * @return \Illuminate\Http\Response
     */
    public function edit(salaries $salaries)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatesalariesRequest  $request
     * @param  \App\Models\salaries  $salaries
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatesalariesRequest $request, salaries $salaries)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\salaries  $salaries
     * @return \Illuminate\Http\Response
     */
    public function destroy(salaries $salaries)
    {
        //
    }
}
