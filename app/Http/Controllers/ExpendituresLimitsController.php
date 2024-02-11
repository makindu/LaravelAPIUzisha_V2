<?php

namespace App\Http\Controllers;

use App\Models\expendituresLimits;
use App\Http\Requests\StoreexpendituresLimitsRequest;
use App\Http\Requests\UpdateexpendituresLimitsRequest;
use App\Models\UsersExpendituresLimits;
use Illuminate\Http\Request;

class ExpendituresLimitsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseId)
    {
        $list=collect(expendituresLimits::where('enterprise_id','=',$enterpriseId)->get());
        $listdata=$list->map( function ($item,$key){
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
     * @param  \App\Http\Requests\StoreexpendituresLimitsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreexpendituresLimitsRequest $request)
    {
        if(!isset($request)){
            $request['money_id']=$this->defaultmoney($request['enterprise_id'])['id'];
        }
        return $this->show(expendituresLimits::create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\expendituresLimits  $expendituresLimits
     * @return \Illuminate\Http\Response
     */
    public function show(expendituresLimits $expendituresLimits)
    {
        return expendituresLimits::leftjoin('moneys as M','expenditures_limits.money_id','=','M.id')
                                ->where('expenditures_limits.id','=',$expendituresLimits->id)->get(['expenditures_limits.*','M.money_name','M.abreviation'])->first();
    }

    /**
     * get one
     */
    public function getUsersForOne($expendituresLimits){
        $users=[];
        $find=expendituresLimits::find($expendituresLimits);
        if($find){
            $users=UsersExpendituresLimits::join('users as U','users_expenditures_limits.user_id','=','U.id')->where('users_expenditures_limits.limit_id','=',$expendituresLimits)->get(['U.user_name','U.user_mail','U.avatar','U.note','users_expenditures_limits.*']);
        }
       
        return $users;
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\expendituresLimits  $expendituresLimits
     * @return \Illuminate\Http\Response
     */
    public function edit(expendituresLimits $expendituresLimits)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateexpendituresLimitsRequest  $request
     * @param  \App\Models\expendituresLimits  $expendituresLimits
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateexpendituresLimitsRequest $request, expendituresLimits $expendituresLimits)
    {
        //
    }

    /**
     * update second method
     */
    public function update2(Request $request,$id)
    {
        $limit=expendituresLimits::find($id);
        $limit->update($request->all());

        return $this->show(expendituresLimits::find($id));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\expendituresLimits  $expendituresLimits
     * @return \Illuminate\Http\Response
     */
    public function destroy(expendituresLimits $expendituresLimits)
    {
        //
    }

    /**
     * second destroy method
     */
    public function destroy2($limitId){
        $message="unknown";
        $find=expendituresLimits::find($limitId);
        if ($find) {
            $message="find";
            $deleted=$find->delete();
        }

        if($deleted){
            //delete all affectations
            UsersExpendituresLimits::where('limit_id','=',$limitId)->delete();
            $message="deleted";
        }

        return response()->json([
            'message'=>$message,
            'limit'=>$find
        ]);
    }
}
