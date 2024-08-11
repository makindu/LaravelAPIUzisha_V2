<?php

namespace App\Http\Controllers;

use App\Models\self_references;
use App\Http\Requests\Storeself_referencesRequest;
use App\Http\Requests\Updateself_referencesRequest;
use Illuminate\Http\Request;

class SelfReferencesController extends Controller
{
   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return self_references::all();
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
        return self_references::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\self_references  $self_references
     * @return \Illuminate\Http\Response
     */
    public function show(self_references $self_references)
    {
        return self_references::find($self_references);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\self_references  $self_references
     * @return \Illuminate\Http\Response
     */
    public function edit(self_references $self_references)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Updateself_referencesRequest  $request
     * @param  \App\Models\self_references  $self_references
     * @return \Illuminate\Http\Response
     */
    public function update(Updateself_referencesRequest $request, self_references $self_references)
    {
        $element = self_references::find($self_references);
        return $element->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\self_references  $self_references
     * @return \Illuminate\Http\Response
     */
    public function destroy(self_references $self_references)
    {
        return self_references::destroy($self_references);
    }

}
