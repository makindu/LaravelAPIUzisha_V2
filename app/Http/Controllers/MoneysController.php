<?php

namespace App\Http\Controllers;

use App\Models\moneys;
use App\Models\requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoremoneysRequest;
use App\Http\Requests\UpdatemoneysRequest;
use Symfony\Component\Translation\Util\ArrayConverter;

class MoneysController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise_id)
    {
        $list=moneys::where('enterprise_id','=',$enterprise_id)->get();
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $all=moneys::get();
        if(count($all)==0){
            $request['principal']=1;
        }else{}
        return moneys::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\moneys  $moneys
     * @return \Illuminate\Http\Response
     */
    public function show(moneys $moneys)
    {
        return moneys::find($moneys);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\moneys  $moneys
     * @return \Illuminate\Http\Response
     */
    public function edit(moneys $moneys)
    {
        //
    }
    
    public function update2($id,Request $request)
    {
        if($request['principal']==1){
            DB::update('update moneys set principal = ? ',[0]);
        }
        $element = moneys::find($id);
        $element->update($request->all());
        return $this->show($element);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\moneys  $moneys
     * @return \Illuminate\Http\Response
     */
    public function destroy(moneys $moneys)
    {
        return moneys::destroy($moneys);
    }
    
    public function destroy2($id)
    {
        $money=moneys::find($id);
        return $money->delete();
    }

}
