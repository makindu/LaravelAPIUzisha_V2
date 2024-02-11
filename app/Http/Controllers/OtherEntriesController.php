<?php

namespace App\Http\Controllers;

use App\Models\OtherEntries;
use App\Http\Requests\StoreOtherEntriesRequest;
use App\Http\Requests\UpdateOtherEntriesRequest;
use Illuminate\Http\Request;

class OtherEntriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseid)
    {
        $list=collect(OtherEntries::where('enterprise_id','=',$enterpriseid)->get());
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $listdata;
    }

    public function byaccount(Request $request){

        if(isset($request['from']) && !empty($request['from']) && isset($request['to']) && !empty($request['to'])){
            $list=collect(OtherEntries::where('account_id','=',$request->account_id)
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->get());
            $listdata=$list->map(function ($item,$key){
                return $this->show($item);
            });
            return $listdata;
        }
        else{
            $list=collect(OtherEntries::where('account_id','=',$request->account_id)->get());
            $listdata=$list->map(function ($item,$key){
                return $this->show($item);
            });
            return $listdata;
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
     * @param  \App\Http\Requests\StoreOtherEntriesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOtherEntriesRequest $request)
    {
        if (!$request['uuid']) {
            $request['uuid']=$this->getUuId('OE','C');
        }

        if(!$request['money_id']){
            $defaultmoney=$this->defaultmoney($request['enterprise_id']);
            $request['money_id']=$defaultmoney['id'];
        }
        return $this->show(OtherEntries::create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OtherEntries  $otherEntries
     * @return \Illuminate\Http\Response
     */
    public function show(OtherEntries $otherEntries)
    {
        return OtherEntries::leftjoin('moneys as M','other_entries.money_id','=','M.id')
        ->leftjoin('accounts as A','other_entries.account_id','=','A.id')
        ->leftjoin('users as U','other_entries.user_id','=','U.id')
        ->where('other_entries.id','=',$otherEntries->id)
        ->get(['M.money_name','M.abreviation','A.name as account_name','U.user_name','other_entries.*'])[0];
    }

    /**
     * Done by a specific user
     */
    public function doneby(Request $request){

        if(isset($request['from']) && !empty($request['from']) && isset($request['to']) && !empty($request['to'])){
            $list=collect(OtherEntries::where('user_id','=',$request->user_id)
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->get());
            $listdata=$list->map(function ($item,$key){
                return $this->show($item);
            });
            return $listdata;
        }
        else{
            $from=date('Y-m-d');
            $list=collect(OtherEntries::where('user_id','=',$request->user_id)
            ->whereBetween('created_at',[$from.' 00:00:00',$from.' 23:59:59'])->get());
            $listdata=$list->map(function ($item,$key){
                return $this->show($item);
            });
            return $listdata;
        }
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OtherEntries  $otherEntries
     * @return \Illuminate\Http\Response
     */
    public function edit(OtherEntries $otherEntries)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateOtherEntriesRequest  $request
     * @param  \App\Models\OtherEntries  $otherEntries
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOtherEntriesRequest $request, OtherEntries $otherEntries)
    {

    }

    public function update2(Request $request,$otherentry)
    {
        OtherEntries::find($otherentry)->update($request->all());
        return OtherEntries::find($otherentry);
    }

    public function delete($entry_id){
        return OtherEntries::find($entry_id)->delete();
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OtherEntries  $otherEntries
     * @return \Illuminate\Http\Response
     */
    public function destroy(OtherEntries $otherEntries)
    {
        //
    }
}
