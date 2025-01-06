<?php

namespace App\Http\Controllers;

use App\Models\uzishafuelconsumption;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreuzishafuelconsumptionRequest;
use App\Http\Requests\UpdateuzishafuelconsumptionRequest;
use Exception;
use Illuminate\Http\Request;

class UzishafuelconsumptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (empty($request['from']) && empty($request['to'])) {
            $request['from']=date('Y-m-d');
            $request['to']=date('Y-m-d');
        } 

        $list=uzishafuelconsumption::where('enterprise_id',$request['enterprise_id'])
            ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->get();

        return $list;
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
     * @param  \App\Http\Requests\StoreuzishafuelconsumptionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreuzishafuelconsumptionRequest $request)
    {
        try {
             $newoperation=uzishafuelconsumption::create($request->all());
             return response()->json([
                "status"=>200,
                "message"=>"success",
                "error"=>null,
                "data"=>$newoperation 
            ]);
        } catch (Exception $th) {
            return response()->json([
                "status"=>500,
                "message"=>"error",
                "error"=>$th->getMessage(),
                "data"=>null
            ]); 
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\uzishafuelconsumption  $uzishafuelconsumption
     * @return \Illuminate\Http\Response
     */
    public function show(uzishafuelconsumption $uzishafuelconsumption)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\uzishafuelconsumption  $uzishafuelconsumption
     * @return \Illuminate\Http\Response
     */
    public function edit(uzishafuelconsumption $uzishafuelconsumption)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateuzishafuelconsumptionRequest  $request
     * @param  \App\Models\uzishafuelconsumption  $uzishafuelconsumption
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateuzishafuelconsumptionRequest $request, uzishafuelconsumption $uzishafuelconsumption)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\uzishafuelconsumption  $uzishafuelconsumption
     * @return \Illuminate\Http\Response
     */
    public function destroy(uzishafuelconsumption $uzishafuelconsumption)
    {
        //
    }
}
