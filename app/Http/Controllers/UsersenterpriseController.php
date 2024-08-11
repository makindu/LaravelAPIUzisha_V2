<?php

namespace App\Http\Controllers;

use App\Models\usersenterprise;
use App\Http\Requests\StoreusersenterpriseRequest;
use App\Http\Requests\UpdateusersenterpriseRequest;

class UsersenterpriseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseid)
    {
        $list=collect(usersenterprise::where('enterprise_id','=',$enterpriseid)->get());
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
     * @param  \App\Http\Requests\StoreusersenterpriseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreusersenterpriseRequest $request)
    {
        return $this->show(usersenterprise::create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\usersenterprise  $usersenterprise
     * @return \Illuminate\Http\Response
     */
    public function show(usersenterprise $usersenterprise)
    {
        return  usersenterprise::leftjoin('Users as U', 'usersenterprise.user_id','=','U.id')
        ->where('usersenterprises.id', '=', $usersenterprise->id)
        ->get(['U.*'])[0];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\usersenterprise  $usersenterprise
     * @return \Illuminate\Http\Response
     */
    public function edit(usersenterprise $usersenterprise)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateusersenterpriseRequest  $request
     * @param  \App\Models\usersenterprise  $usersenterprise
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateusersenterpriseRequest $request, usersenterprise $usersenterprise)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\usersenterprise  $usersenterprise
     * @return \Illuminate\Http\Response
     */
    public function destroy(usersenterprise $usersenterprise)
    {
        //
    }
}
