<?php

namespace App\Http\Controllers;

use App\Models\plans;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreplansRequest;
use App\Http\Requests\UpdateplansRequest;
use Exception;

class PlansController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $list=plans::all();
            return response()->json([
                "status"=>200,
                "message"=>"success",
                "error"=>null,
                "data"=>$list
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
     * @param  \App\Http\Requests\StoreplansRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreplansRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\plans  $plans
     * @return \Illuminate\Http\Response
     */
    public function show(plans $plans)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\plans  $plans
     * @return \Illuminate\Http\Response
     */
    public function edit(plans $plans)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateplansRequest  $request
     * @param  \App\Models\plans  $plans
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateplansRequest $request, plans $plans)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\plans  $plans
     * @return \Illuminate\Http\Response
     */
    public function destroy(plans $plans)
    {
        //
    }
}
