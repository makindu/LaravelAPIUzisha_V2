<?php

namespace App\Http\Controllers;

use App\Models\nbrdecisionteam_validation;
use App\Http\Requests\Storenbrdecisionteam_validationRequest;
use App\Http\Requests\Updatenbrdecisionteam_validationRequest;
use Illuminate\Http\Request;

class NbrdecisionteamValidationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return nbrdecisionteam_validation::all();
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
        $old =nbrdecisionteam_validation::all();

        if(count($old)>0){
            $element=nbrdecisionteam_validation::find($old[0]->id);
            $element->update($request->all());
            return $this->show($element);
        }
        else{
            return nbrdecisionteam_validation::create($request->all());
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\nbrdecisionteam_validation  $nbrdecisionteam_validation
     * @return \Illuminate\Http\Response
     */
    public function show(nbrdecisionteam_validation $nbrdecisionteam_validation)
    {
        return nbrdecisionteam_validation::find($nbrdecisionteam_validation);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\nbrdecisionteam_validation  $nbrdecisionteam_validation
     * @return \Illuminate\Http\Response
     */
    public function edit(nbrdecisionteam_validation $nbrdecisionteam_validation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Updatenbrdecisionteam_validationRequest  $request
     * @param  \App\Models\nbrdecisionteam_validation  $nbrdecisionteam_validation
     * @return \Illuminate\Http\Response
     */
    public function update(Updatenbrdecisionteam_validationRequest $request, nbrdecisionteam_validation $nbrdecisionteam_validation)
    {
        $element = nbrdecisionteam_validation::find($nbrdecisionteam_validation);
        return $element->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\nbrdecisionteam_validation  $nbrdecisionteam_validation
     * @return \Illuminate\Http\Response
     */
    public function destroy(nbrdecisionteam_validation $nbrdecisionteam_validation)
    {
        return nbrdecisionteam_validation::destroy($nbrdecisionteam_validation);
    }

}
