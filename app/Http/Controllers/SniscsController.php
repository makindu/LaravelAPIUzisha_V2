<?php

namespace App\Http\Controllers;

use App\Models\SnisCs1_729;
use App\Models\SnisCs894_1694;
use App\Http\Requests\UpdatesniscsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class SniscsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data=SnisCs1_729::
        join('sniscs894_1694','sniscs1_729.id','=','sniscs894_1694.id_operation')
        ->get();
      
        return $data;
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
        $sniscs1=SnisCs1_729::create($request->all());
        $request['id_operation']=$sniscs1->id;
        $sniscs2=SnisCs894_1694::create($request->all());
        
        return $request;
    }

    public function sniscs8941694(Request $request){

        $sniscs2=SnisCs894_1694::create($request->all());

        return $sniscs2;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SnisCs1_729  $sniscs
     * @return \Illuminate\Http\Response
     */
    public function show(SnisCs1_729 $sniscs)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SnisCs1_729 $sniscs
     * @return \Illuminate\Http\Response
     */
    public function edit(SnisCs1_729 $sniscs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatesniscsRequest  $request
     * @param  \App\Models\SnisCs1_729  $sniscs
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatesniscsRequest $request, SnisCs1_729 $sniscs)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SnisCs1_729 $sniscs
     * @return \Illuminate\Http\Response
     */
    public function destroy(SnisCs1_729 $sniscs)
    {
        //
    }
}
