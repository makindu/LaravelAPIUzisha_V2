<?php

namespace App\Http\Controllers;

use App\Models\decision_chiefdepartments;
use App\Http\Requests\Storedecision_chiefdepartmentsRequest;
use App\Http\Requests\Updatedecision_chiefdepartmentsRequest;
use App\Models\decision_decisionteam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DecisionChiefdepartmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return decision_chiefdepartments::all();
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
        //if not in decision team
        $ifnotindecision= decision_decisionteam::
        where('request_id','=',$request->request_id)
        ->get();
        if(count($ifnotindecision)>0){
            $message='unauthorized';
            $data['output']=$request;
            $data['message']=$message;
            return $data;
        }else{
            //check if already validate
            $ifexist= decision_chiefdepartments::
                where('request_id','=',$request->request_id)
                ->get();
    
           if(count($ifexist)>0){
             $funded=decision_chiefdepartments::find($ifexist[0]->id);
             $funded->update($request->all());
             //update status's request
                $this->updaterequeststatus($funded->request_id,'chefdepart');
                $message='updated';
                $data['output']=$funded;
                $data['message']=$message;
                return $data;
            }else{
                $new = decision_chiefdepartments::create($request->all());
                $this->updaterequeststatus($new->request_id,'chefdepart');
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
     * @param  \App\Models\decision_chiefdepartments  $decision_chiefdepartments
     * @return \Illuminate\Http\Response
     */
    public function show(decision_chiefdepartments $decision_chiefdepartments)
    {
        return decision_chiefdepartments::find($decision_chiefdepartments);
    }

    public function getsingledecision($id){

        $data= decision_chiefdepartments::
                where('request_id','=',$id)
                ->get();
        return $data;
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\decision_chiefdepartments  $decision_chiefdepartments
     * @return \Illuminate\Http\Response
     */
    public function edit(decision_chiefdepartments $decision_chiefdepartments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Updatedecision_chiefdepartmentsRequest  $request
     * @param  \App\Models\decision_chiefdepartments  $decision_chiefdepartments
     * @return \Illuminate\Http\Response
     */
    public function update(Updatedecision_chiefdepartmentsRequest $request, decision_chiefdepartments $decision_chiefdepartments)
    {
        $element = decision_chiefdepartments::find($decision_chiefdepartments);
        return $element->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\decision_chiefdepartments  $decision_chiefdepartments
     * @return \Illuminate\Http\Response
     */
    public function destroy(decision_chiefdepartments $decision_chiefdepartments)
    {
        return decision_chiefdepartments::destroy($decision_chiefdepartments);
    }

}
