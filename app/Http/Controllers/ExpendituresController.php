<?php

namespace App\Http\Controllers;

use App\Models\Expenditures;
use Illuminate\Http\Request;
use App\Models\UsersExpendituresLimits;
use App\Http\Requests\StoreExpendituresRequest;
use App\Http\Requests\UpdateExpendituresRequest;
use stdClass;

class ExpendituresController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseid)
    {
        $list=collect(Expenditures::where('enterprise_id','=',$enterpriseid)->get());
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $listdata;
    }

    /**
     * Collect all expenditures done by a user
     */
    public function doneby(Request $request){

        if(isset($request['from']) && !empty($request['from']) && isset($request['to']) && !empty($request['to'])){
            $list=collect(Expenditures::where('user_id','=',$request->user_id)
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->get());
            $listdata=$list->map(function ($item,$key){
                return $this->show($item);
            });
            return $listdata;
        }
        else{
            $from=date('Y-m-d');
            $list=collect(Expenditures::where('user_id','=',$request->user_id)
            ->whereBetween('created_at',[$from.' 00:00:00',$from.' 23:59:59'])->get());
            $listdata=$list->map(function ($item,$key){
                return $this->show($item);
            });
            return $listdata;
        }
    }

    public function byaccount(Request $request){

        if(isset($request['from']) && !empty($request['from']) && isset($request['to']) && !empty($request['to'])){
            $list=collect(Expenditures::where('account_id','=',$request->account_id)
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->get());
            $listdata=$list->map(function ($item,$key){
                return $this->show($item);
            });
            return $listdata;
        }
        else{
            $list=collect(Expenditures::where('account_id','=',$request->account_id)->get());
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
     * @param  \App\Http\Requests\StoreExpendituresRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreExpendituresRequest $request)
    {
        $response= new stdClass;
        $message="unknown";
        if (!$request['uuid']) {
            $request['uuid']=$this->getUuId('EX','C');
        }
        
        if(!$request['money_id']){
            $defaultmoney=$this->defaultmoney($request['enterprise_id']);
            $request['money_id']=$defaultmoney['id'];
        }

        $ifexists=UsersExpendituresLimits::join('expenditures_limits as EL','users_expenditures_limits.limit_id','=','EL.id')->where('users_expenditures_limits.user_id','=',$request['user_id'])->get()->first();
        if($ifexists){
            if ($request['amount']<=$ifexists['maximum']) {
                $message="success";
                $response=$this->show(Expenditures::create($request->all()));
            }else{
                $message="unauthorized";
            }
        }else{
            $message="success";
            $response=$this->show(Expenditures::create($request->all()));
        }
        $response->message=$message;
        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Expenditures  $expenditures
     * @return \Illuminate\Http\Response
     */
    public function show(Expenditures $expenditures)
    {
        return Expenditures::leftjoin('moneys as M','expenditures.money_id','=','M.id')
        ->leftjoin('accounts as A','expenditures.account_id','=','A.id')
        ->leftjoin('users as U','expenditures.user_id','=','U.id')
        ->where('expenditures.id','=',$expenditures->id)
        ->get(['M.money_name','M.abreviation','A.name as account_name','U.user_name','expenditures.*'])[0];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Expenditures  $expenditures
     * @return \Illuminate\Http\Response
     */
    public function edit(Expenditures $expenditures)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateExpendituresRequest  $request
     * @param  \App\Models\Expenditures  $expenditures
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateExpendituresRequest $request, Expenditures $expenditures)
    {
        return $this->show(Expenditures::find( $expenditures->update($request->all())));
    }

    /**
     * delete operation
     */
    public function delete($expenditures){
        $expenditures=Expenditures::find($expenditures);
        return $expenditures->delete();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Expenditures  $expenditures
     * @return \Illuminate\Http\Response
     */
    public function destroy(Expenditures $expenditures)
    {
        $expenditures=Expenditures::find($expenditures);
        return Expenditures::destroy($expenditures);
    }
}
