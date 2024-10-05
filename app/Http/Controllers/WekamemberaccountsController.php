<?php

namespace App\Http\Controllers;

use App\Models\wekamemberaccounts;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorewekamemberaccountsRequest;
use App\Http\Requests\UpdatewekamemberaccountsRequest;
use App\Models\wekaAccountsTransactions;

class WekamemberaccountsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise)
    {
        //
    }

    /**
     * get all accounts paginated
     */
    public function allaccounts($user){
        $list=[];
        $actualuser=$this->getinfosuser($user);
        $ese=$this->getEse($user);
        if ($actualuser) {

            if ($actualuser['user_type']!=='super_admin') {
                $list= wekamemberaccounts::leftjoin('users as U', 'wekamemberaccounts.user_id','=','U.id')
                ->leftjoin('moneys as M', 'wekamemberaccounts.money_id','=','M.id')
                ->where('user_id','=',$user)
                ->get(['M.abreviation as money_abreviation', 'U.user_name', 'wekamemberaccounts.*']);
            }
            else{
                $list= wekamemberaccounts::leftjoin('users as U', 'wekamemberaccounts.user_id','=','U.id')
                ->leftjoin('moneys as M', 'wekamemberaccounts.money_id','=','M.id')
                ->where('wekamemberaccounts.enterprise_id',$ese->id)
                ->get(['M.abreviation as money_abreviation', 'U.user_name', 'wekamemberaccounts.*']);
            }

        }
         
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
     * @param  \App\Http\Requests\StorewekamemberaccountsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorewekamemberaccountsRequest $request)
    {
        if (isset($request['sold']) &&  $request['sold']>0) {
            # code...
        }else{
            $request['sold']=0;
        }
               
        $newaccount=wekamemberaccounts::create($request->all());
        //make a new entry
        if($newaccount->sold>0){
            wekaAccountsTransactions::create(
                [
                    'amount'=>$newaccount->sold,
                    'done_at'=>date('Y-m-d'),
                    'user_id'=>$request->created_by,
                    'motif'=>'Balance d\'ouverture',
                    'type'=>'entry',
                    'enterprise_id'=>$request->enterprise_id,
                    'uuid'=>$this->getUuId('C','AT'),
                    'sold_before'=>0,
                    'sold_after'=>$newaccount->sold,
                ]
            );
        }
       
        return $this->show($newaccount);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\wekamemberaccounts  $wekamemberaccounts
     * @return \Illuminate\Http\Response
     */
    public function show(wekamemberaccounts $wekamemberaccounts)
    {
       return wekamemberaccounts::leftjoin('users as U', 'wekamemberaccounts.user_id','=','U.id')
        ->leftjoin('moneys as M', 'wekamemberaccounts.money_id','=','M.id')
        ->where('wekamemberaccounts.id',$wekamemberaccounts->id)->first(['M.abreviation as money_abreviation', 'U.user_name', 'wekamemberaccounts.*']);
    }

    public function membersaccounts($member){
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\wekamemberaccounts  $wekamemberaccounts
     * @return \Illuminate\Http\Response
     */
    public function edit(wekamemberaccounts $wekamemberaccounts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatewekamemberaccountsRequest  $request
     * @param  \App\Models\wekamemberaccounts  $wekamemberaccounts
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatewekamemberaccountsRequest $request, wekamemberaccounts $wekamemberaccounts)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\wekamemberaccounts  $wekamemberaccounts
     * @return \Illuminate\Http\Response
     */
    public function destroy(wekamemberaccounts $wekamemberaccounts)
    {
        //
    }
}
