<?php

namespace App\Http\Controllers;

use App\Models\decision_team;
use App\Http\Requests\Storedecision_teamRequest;
use App\Http\Requests\Updatedecision_teamRequest;
use App\Http\Resources\DecisionTeamResource;
use Illuminate\Http\Request;

class DecisionTeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list=collect(decision_team::all());
        $listdata=$list->map( function ($item,$key){
            return $this->show($item);
        });

        return $listdata;
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
        return $this->show(decision_team::create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\decision_team  $decision_team
     * @return \Illuminate\Http\Response
     */
    public function show(decision_team $decision_team)
    {
        return new DecisionTeamResource($decision_team);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\decision_team  $decision_team
     * @return \Illuminate\Http\Response
     */
    public function edit(decision_team $decision_team)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Updatedecision_teamRequest  $request
     * @param  \App\Models\decision_team  $decision_team
     * @return \Illuminate\Http\Response
     */
    public function update(Updatedecision_teamRequest $request, decision_team $decision_team)
    {
        $element = decision_team::find($decision_team);
        return $element->update($request->all());
    }

    /**
     * update a specific resource in storage
     */
    public function update2(Request $request,$id)
    {
        $element = decision_team::find($id);
        $element->update($request->all());
        return $this->show($element);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\decision_team  $decision_team
     * @return \Illuminate\Http\Response
     */
    public function destroy(decision_team $decision_team)
    {
        return decision_team::destroy($decision_team);
    }
    
    public function destroy2($id)
    {
        $geting=decision_team::find($id);
        return $geting->delete();
    }

}
