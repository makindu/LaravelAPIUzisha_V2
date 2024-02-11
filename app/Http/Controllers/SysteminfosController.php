<?php

namespace App\Http\Controllers;

use App\Models\systeminfos;
use App\Http\Requests\StoresysteminfosRequest;
use App\Http\Requests\UpdatesysteminfosRequest;
use Illuminate\Http\Request;

class SysteminfosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return systeminfos::all();
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
        return systeminfos::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\systeminfos  $systeminfos
     * @return \Illuminate\Http\Response
     */
    public function show(systeminfos $systeminfos)
    {
        return systeminfos::find($systeminfos);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\systeminfos  $systeminfos
     * @return \Illuminate\Http\Response
     */
    public function edit(systeminfos $systeminfos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatesysteminfosRequest  $request
     * @param  \App\Models\systeminfos  $systeminfos
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatesysteminfosRequest $request, systeminfos $systeminfos)
    {
        $element = systeminfos::find($systeminfos);
        return $element->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\systeminfos  $systeminfos
     * @return \Illuminate\Http\Response
     */
    public function destroy(systeminfos $systeminfos)
    {
        return systeminfos::destroy($systeminfos);
    }

}
