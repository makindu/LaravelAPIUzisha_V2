<?php

namespace App\Http\Controllers;

use App\Models\Accounts;
use App\Http\Requests\StoreAccountsRequest;
use App\Http\Requests\UpdateAccountsRequest;
use App\Models\User;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise_id)
    {
        return Accounts::where('enterprise_id','=',$enterprise_id)->get();
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
     * importing data
     */
    public function importation(Request $request){
        $data=[];
        if(count($request->data)>0){
            foreach ($request->data as $account) {
                if ( $newAccount=$this->store(new StoreAccountsRequest($account))) {
                    array_push($data,$newAccount);
                }
            }
        }

        return $data;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAccountsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAccountsRequest $request)
    {
        return Accounts::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Accounts  $accounts
     * @return \Illuminate\Http\Response
     */
    public function show(Accounts $accounts)
    {
        return Accounts::find($accounts);
    }
    
    public function showone($account_id)
    {
        return Accounts::find($account_id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Accounts  $accounts
     * @return \Illuminate\Http\Response
     */
    public function edit(Accounts $accounts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAccountsRequest  $request
     * @param  \App\Models\Accounts  $accounts
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAccountsRequest $request, Accounts $accounts)
    {
        return $accounts->update($request->all());
    }
    
    public function update2(UpdateAccountsRequest $request,$accounts)
    {
        Accounts::find($accounts)->update($request->all());
        return Accounts::find($accounts);
    }

    public function delete($account_id){
         return Accounts::find($account_id)->delete();
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Accounts  $accounts
     * @return \Illuminate\Http\Response
     */
    public function destroy(Accounts $accounts)
    {
        Accounts::destroy($accounts);
    }
}
