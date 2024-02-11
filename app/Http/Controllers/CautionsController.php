<?php

namespace App\Http\Controllers;

use App\Models\Cautions;
use App\Http\Requests\StoreCautionsRequest;
use App\Http\Requests\UpdateCautionsRequest;
use Illuminate\Http\Request;

class CautionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseid)
    {
        $list=collect(Cautions::where('enterprise_id','=',$enterpriseid)->get());
        $listdata=$list->map(function ($item,$key){
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
     * @param  \App\Http\Requests\StoreCautionsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCautionsRequest $request)
    {
        if(isset($request->enterprise_id)){
            //getting default money
            $money=$this->defaultmoney($request->enterprise_id);
            $request['money_id']=$money->id;
        }

        $request['uuid']=$this->getUuId('CA','C');
        return $this->show(Cautions::create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cautions  $cautions
     * @return \Illuminate\Http\Response
     */
    public function show(Cautions $cautions)
    {
        return Cautions::leftjoin('customer_controllers as C', 'cautions.customer_id','=','C.id')
        ->leftjoin('enterprises as E', 'cautions.enterprise_id','=','E.id')
        ->leftjoin('moneys as M', 'cautions.money_id','=','M.id')
        ->where('cautions.id', '=', $cautions->id)
        ->get(['C.customerName','M.money_name','M.abreviation','cautions.*','E.name as enterpriseName'])[0];
    }

     /**
     * for a specific customer
     */
    public function foracustomer($customerid){
        
        $list=collect(Cautions::where('customer_id','=',$customerid)->get());
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $listdata;
    }
    
    /**
     * for a specific customer
     */
    public function FilteredCautionsForACustomer(Request $request){

        if (empty($request['from']) && empty($request['to'])) {
            $request['from']=date('Y-m-d');
            $request['to']=date('Y-m-d');
        } 

        $list=collect(Cautions::whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('customer_id','=',$request['customer_id'])->get());
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });

        return ["cautions"=>$listdata,"from"=> $request['from'],"to"=> $request['to']];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Cautions  $cautions
     * @return \Illuminate\Http\Response
     */
    public function edit(Cautions $cautions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCautionsRequest  $request
     * @param  \App\Models\Cautions  $cautions
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCautionsRequest $request, Cautions $cautions)
    {
        $cautions->update($request->all());
        return $this->show($cautions);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cautions  $cautions
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cautions $cautions)
    {
        return $cautions->delete();
    }
}
