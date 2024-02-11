<?php

namespace App\Http\Controllers;

use App\Models\decision_decisionteam;
use App\Http\Requests\Storedecision_decisionteamRequest;
use App\Http\Requests\Updatedecision_decisionteamRequest;
use App\Models\decision_chiefdepartments;
use App\Models\nbrdecisionteam_validation;
use Illuminate\Http\Request;

class DecisionDecisionteamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return decision_decisionteam::all();
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
        $data=['output'=>[],'message'=>''];
        $message='';
        //if already in decision_chief depart
        $ifnotindecisionchefdepart= decision_chiefdepartments::
        where('request_id','=',$request->request_id)
        ->get();
        if(count($ifnotindecisionchefdepart)<=0){
            $message='unauthorized';
            $data['output']=$request;
            $data['message']=$message;
            return $data;
        }
        //if not in decision team
        $ifnotindecision= decision_decisionteam::
        where('request_id','=',$request->request_id)
        ->get();
        //take nbr of validations allowed
        $nbrvalidation=nbrdecisionteam_validation::all();

        if(count($ifnotindecision)>0 && $nbrvalidation[0]->nbr>=count($ifnotindecision)){
            $message='unauthorized';
            $data['output']=$request;
            $data['message']=$message;
            return $data;
        }else{
            //check if already validate
            $ifexist= decision_decisionteam::
                where('request_id','=',$request->request_id)
                ->get();
    
           if(count($ifexist)>0 && $nbrvalidation[0]->nbr<=count($ifexist)){

             $funded=decision_decisionteam::find($ifexist[0]->id);
             $funded->update($request->all());
             //update status's request
                $this->updaterequeststatus($funded->request_id,'decision');
                $message='updated';
                $data['output']=$funded;
                $data['message']=$message;
                return $data;
            }else{
                $new = decision_decisionteam::create($request->all());
                $this->updaterequeststatus($new->request_id,'decision');
                $message='added';
                $data['output']=$new;
                $data['message']=$message;
                return $data; 
           }  
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\decision_decisionteam  $decision_decisionteam
     * @return \Illuminate\Http\Response
     */
    public function show(decision_decisionteam $decision_decisionteam)
    {
        return decision_decisionteam::find($decision_decisionteam);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\decision_decisionteam  $decision_decisionteam
     * @return \Illuminate\Http\Response
     */
    public function edit(decision_decisionteam $decision_decisionteam)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Updatedecision_decisionteamRequest  $request
     * @param  \App\Models\decision_decisionteam  $decision_decisionteam
     * @return \Illuminate\Http\Response
     */
    public function update(Updatedecision_decisionteamRequest $request, decision_decisionteam $decision_decisionteam)
    {
        $element = decision_decisionteam::find($decision_decisionteam);
        return $element->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\decision_decisionteam  $decision_decisionteam
     * @return \Illuminate\Http\Response
     */
    public function destroy(decision_decisionteam $decision_decisionteam)
    {
        return decision_decisionteam::destroy($decision_decisionteam);
    }

}
