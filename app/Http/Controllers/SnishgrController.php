<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SnisHgr1_831;
use App\Models\SnisHgr831_1662;

class SnishgrController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data=SnisHgr1_831::
        join('snishrg832_1662','snishrg1_831.id','=','snishrg832_1662.id_operation')
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
        $hgr1=SnisHgr1_831::create($request->all());
        $request['id_operation']=$hgr1->id;
        $hgr2=SnisHgr831_1662::create($request->all());
        
        return $request;
    }

    /**
     * Display the specified resource.
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        //
    }
}
