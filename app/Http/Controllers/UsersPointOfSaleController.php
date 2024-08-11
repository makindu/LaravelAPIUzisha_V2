<?php

namespace App\Http\Controllers;

use App\Models\UsersPointOfSale;
use App\Http\Requests\StoreUsersPointOfSaleRequest;
use App\Http\Requests\UpdateUsersPointOfSaleRequest;
use App\Models\User;

class UsersPointOfSaleController extends Controller
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
     * @param  \App\Http\Requests\StoreUsersPointOfSaleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUsersPointOfSaleRequest $request)
    {
        $users=[];
        $userCtrl= new UsersController();
        if ($request && isset($request['users']) && !empty($request['users'])) {
            foreach ($request['users'] as $value) {
                $ifexists=UsersPointOfSale::where('user_id','=',$value['user_id'])->where('pos_id','=',$value['pos_id'])->first();
                if (!$ifexists) {
                    $affectation = UsersPointOfSale::create([
                        'user_id'=>$value['user_id'],
                        'pos_id'=>$value['pos_id']
                    ]);
                    if ($affectation) {
                        array_push($users,$userCtrl->show(User::find($value['user_id'])));
                    }
                }
            }
        }
        
        return $users;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UsersPointOfSale  $usersPointOfSale
     * @return \Illuminate\Http\Response
     */
    public function show(UsersPointOfSale $usersPointOfSale)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UsersPointOfSale  $usersPointOfSale
     * @return \Illuminate\Http\Response
     */
    public function edit(UsersPointOfSale $usersPointOfSale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUsersPointOfSaleRequest  $request
     * @param  \App\Models\UsersPointOfSale  $usersPointOfSale
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUsersPointOfSaleRequest $request, UsersPointOfSale $usersPointOfSale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UsersPointOfSale  $usersPointOfSale
     * @return \Illuminate\Http\Response
     */
    public function destroy(UsersPointOfSale $usersPointOfSale)
    {
        //
    }
}
