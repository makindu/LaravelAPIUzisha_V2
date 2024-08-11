<?php

namespace App\Http\Controllers;

use App\Models\UsersExpendituresLimits;
use App\Http\Requests\StoreUsersExpendituresLimitsRequest;
use App\Http\Requests\UpdateUsersExpendituresLimitsRequest;

class UsersExpendituresLimitsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoreUsersExpendituresLimitsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUsersExpendituresLimitsRequest $request)
    {
        $list=[];
        if(isset($request['list'])){
            foreach ($request['list'] as $value) {
                //test if the user has been already added
                $ifexists=UsersExpendituresLimits::where('user_id','=',$value['user_id'])->get()->first();
                if(!$ifexists){
                    $newEntry=UsersExpendituresLimits::create($value);
                    array_push($list,$this->show($newEntry));
                }
            }
        }

        return $list;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UsersExpendituresLimits  $usersExpendituresLimits
     * @return \Illuminate\Http\Response
     */
    public function show(UsersExpendituresLimits $usersExpendituresLimits)
    {
        return UsersExpendituresLimits::join('users as U','users_expenditures_limits.user_id','=','U.id')->where('users_expenditures_limits.id','=',$usersExpendituresLimits->id)->get(['U.user_name','U.user_mail','U.avatar','U.note','users_expenditures_limits.*'])->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UsersExpendituresLimits  $usersExpendituresLimits
     * @return \Illuminate\Http\Response
     */
    public function edit(UsersExpendituresLimits $usersExpendituresLimits)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUsersExpendituresLimitsRequest  $request
     * @param  \App\Models\UsersExpendituresLimits  $usersExpendituresLimits
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUsersExpendituresLimitsRequest $request, UsersExpendituresLimits $usersExpendituresLimits)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UsersExpendituresLimits  $usersExpendituresLimits
     * @return \Illuminate\Http\Response
     */
    public function destroy(UsersExpendituresLimits $usersExpendituresLimits)
    {
        //
    }

    /**
     * destroy second method
     */
    public function destroy2($id){
        $message="failed";
        $get=UsersExpendituresLimits::find($id);
        if ($get) {
            $get->delete();
            $message="deleted";
        }

        return ['message'=>$message];
    }
}
