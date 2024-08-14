<?php

namespace App\Http\Controllers;

use App\Models\cashbooks;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorecashbooksRequest;
use App\Http\Requests\UpdatecashbooksRequest;

class CashbooksController extends Controller
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
     * @param  \App\Http\Requests\StorecashbooksRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorecashbooksRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\cashbooks  $cashbooks
     * @return \Illuminate\Http\Response
     */
    public function show(cashbooks $cashbooks)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\cashbooks  $cashbooks
     * @return \Illuminate\Http\Response
     */
    public function edit(cashbooks $cashbooks)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatecashbooksRequest  $request
     * @param  \App\Models\cashbooks  $cashbooks
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatecashbooksRequest $request, cashbooks $cashbooks)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\cashbooks  $cashbooks
     * @return \Illuminate\Http\Response
     */
    public function destroy(cashbooks $cashbooks)
    {
        //
    }
}
