<?php

namespace App\Http\Controllers;

use App\Models\funds;
use App\Models\requestHistory;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StorerequestHistoryRequest;
use App\Http\Requests\UpdaterequestHistoryRequest;

class RequestHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list =requestHistory::join('users','request_histories.user_id','=','users.id')->get(['request_histories.*','users.user_name']);
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
     * @param  \App\Http\Requests\StorerequestHistoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorerequestHistoryRequest $request)
    {
        if($request->type=='entry'){
            $newvalue=requestHistory::create($request->all());
            DB::update('update funds set sold =sold + ? where id = ? ',[$request->amount,$request->fund_id]);
        }else{
            //checking sold
            $gettingsold=funds::where('id','=',$request->fund_id)->get('funds.sold')[0];
            $sold=$gettingsold['sold'];
            if($sold>=$request->amount){
                $newvalue=requestHistory::create($request->all());
                DB::update('update funds set sold =sold - ? where id = ? ',[$request->amount,$request->fund_id]); 
            }else{}
        }

        return  $this->show($newvalue);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\requestHistory  $requestHistory
     * @return \Illuminate\Http\Response
     */
    public function show(requestHistory $requestHistory)
    {
        return requestHistory::join('users','request_histories.user_id','=','users.id')->where('request_histories.id','=',$requestHistory->id)->get(['request_histories.*','users.user_name'])[0];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\requestHistory  $requestHistory
     * @return \Illuminate\Http\Response
     */
    public function edit(requestHistory $requestHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdaterequestHistoryRequest  $request
     * @param  \App\Models\requestHistory  $requestHistory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdaterequestHistoryRequest $request, requestHistory $requestHistory)
    {
        //
    }

    public function getbyfund($fund){
        $list =requestHistory::join('users','request_histories.user_id','=','users.id')->where('fund_id','=',$fund)->get(['request_histories.*','users.user_name']);
        return $list;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\requestHistory  $requestHistory
     * @return \Illuminate\Http\Response
     */
    public function destroy(requestHistory $requestHistory)
    {
        //
    }
}
