<?php

namespace App\Http\Controllers;

use App\Models\sub_departements;
use App\Http\Requests\Storesub_departementsRequest;
use App\Http\Requests\Updatesub_departementsRequest;
use Illuminate\Http\Request;

class SubDepartementsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return sub_departements::all();
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
        return sub_departements::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\sub_departements  $sub_departements
     * @return \Illuminate\Http\Response
     */
    public function show(sub_departements $sub_departements)
    {
        return sub_departements::find($sub_departements);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\sub_departements  $sub_departements
     * @return \Illuminate\Http\Response
     */
    public function edit(sub_departements $sub_departements)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Updatesub_departementsRequest  $request
     * @param  \App\Models\sub_departements  $sub_departements
     * @return \Illuminate\Http\Response
     */
    public function update(Updatesub_departementsRequest $request, sub_departements $sub_departements)
    {
        $element = sub_departements::find($sub_departements);
        return $element->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\sub_departements  $sub_departements
     * @return \Illuminate\Http\Response
     */
    public function destroy(sub_departements $sub_departements)
    {
        return sub_departements::destroy($sub_departements);
    }

}
