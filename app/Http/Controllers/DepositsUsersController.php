<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\DepositsUsers;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreDepositsUsersRequest;
use App\Http\Requests\UpdateDepositsUsersRequest;

class DepositsUsersController extends Controller
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
     * @param  \App\Http\Requests\StoreDepositsUsersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDepositsUsersRequest $request)
    {
        //check if the user is already affected to the deposit
        $if_exists=DepositsUsers::where('user_id','=',$request->user_id)->where('deposit_id','=',$request->deposit_id)->get();
        if(count($if_exists)>0){
            return 'already';
        }else{
            $new=DepositsUsers::create($request->all());
            return $this->show($new);
        } 
       
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DepositsUsers  $depositsUsers
     * @return \Illuminate\Http\Response
     */
    public function show(DepositsUsers $depositsUsers)
    {
        return DepositsUsers::join('users as U', 'deposits_users.user_id','=','U.id')
        ->join('deposit_controllers as D','deposits_users.deposit_id','=','D.id')
        ->where('deposits_users.id', '=', $depositsUsers->id)
        ->get(['D.name as deposit_name','D.description as deposit_description','U.user_name','U.user_mail','U.avatar','U.note','U.status','deposits_users.*'])[0];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DepositsUsers  $depositsUsers
     * @return \Illuminate\Http\Response
     */
    public function edit(DepositsUsers $depositsUsers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDepositsUsersRequest  $request
     * @param  \App\Models\DepositsUsers  $depositsUsers
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDepositsUsersRequest $request, DepositsUsers $depositsUsers)
    {
        //
    }

    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DepositsUsers  $depositsUsers
     * @return \Illuminate\Http\Response
     */
    public function destroy(DepositsUsers $depositsUsers)
    {
        return DepositsUsers::destroy($depositsUsers);
    }

    public function deleteaffectation($affectationId){
        $affectation=DepositsUsers::find($affectationId);
        return $affectation->delete();
    }

    public function updateaffectation(Request $request,$idaffectation){

        if($request->level=='chief'){
            DB::update('update deposits_users set level = ? where deposit_id = ? ',['simple',$request->deposit_id]);
        }
        
        $affectation=DepositsUsers::find($idaffectation);
        $affectation->update($request->all());
        return $this->show($affectation);
    }
}
