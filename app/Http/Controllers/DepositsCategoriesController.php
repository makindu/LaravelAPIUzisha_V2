<?php

namespace App\Http\Controllers;

use App\Models\DepositsCategories;
use App\Http\Requests\StoreDepositsCategoriesRequest;
use App\Http\Requests\UpdateDepositsCategoriesRequest;

class DepositsCategoriesController extends Controller
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
     * @param  \App\Http\Requests\StoreDepositsCategoriesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDepositsCategoriesRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DepositsCategories  $depositsCategories
     * @return \Illuminate\Http\Response
     */
    public function show(DepositsCategories $depositsCategories)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DepositsCategories  $depositsCategories
     * @return \Illuminate\Http\Response
     */
    public function edit(DepositsCategories $depositsCategories)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDepositsCategoriesRequest  $request
     * @param  \App\Models\DepositsCategories  $depositsCategories
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDepositsCategoriesRequest $request, DepositsCategories $depositsCategories)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DepositsCategories  $depositsCategories
     * @return \Illuminate\Http\Response
     */
    public function destroy(DepositsCategories $depositsCategories)
    {
        //
    }
}
