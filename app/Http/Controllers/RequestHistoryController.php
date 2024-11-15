<?php

namespace App\Http\Controllers;

use App\Models\funds;
use App\Models\requestHistory;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StorerequestHistoryRequest;
use App\Http\Requests\UpdaterequestHistoryRequest;
use Illuminate\Http\Request;

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
            $fund=funds::find($request->fund_id);
            $request['sold']=$fund->sold+$request->amount;
            $newvalue=requestHistory::create($request->all());
            DB::update('update funds set sold =sold + ? where id = ? ',[$request->amount,$request->fund_id]);
            return  $this->show($newvalue);
        }else{
            //checking sold
            $gettingsold=funds::find($request->fund_id);
            $sold=$gettingsold['sold'];

            if($sold>=$request->amount){
                $request['sold']=$sold-$request->amount;
                $newvalue=requestHistory::create($request->all());
                DB::update('update funds set sold =sold - ? where id = ? ',[$request->amount,$request->fund_id]); 
                return  $this->show($newvalue);
            }
            else{
                return response()->json([
                    "message"=>"error",
                    "error"=>"no type operation",
                    "data"=>null
                ]);
            }
        }
    }

    /**
     * save multiples
     */
    public function savemultiple(Request $request){
        $data=[];
        // return $request;
        if ($request->data && count($request->data)>0) {
            try {
                foreach ($request->data as  $item) {
                    array_push($data,$this->store(new StorerequestHistoryRequest($item)));
                }

                return response()->json([
                    "status"=>200,
                    "message"=>"success",
                    "error"=>null,
                    "data"=>$data
                ]);
            } catch (\Throwable $th) {
                return response()->json([
                    "status"=>500,
                    "message"=>"error occured",
                    "error"=>$th,
                    "data"=>null
                ]);
            }
          
        }else{
            return response()->json([
                "status"=>500,
                "message"=>"error occured",
                "error"=>"no data sent",
                "data"=>null
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\requestHistory  $requestHistory
     * @return \Illuminate\Http\Response
     */
    public function show(requestHistory $requestHistory)
    {
        return requestHistory::join('users','request_histories.user_id','=','users.id')
                            ->join('funds as F','request_histories.fund_id','F.id')
                            ->join('moneys as M','F.money_id','M.id')
                            ->leftjoin('accounts as A','request_histories.account_id','A.id')
                            ->where('request_histories.id','=',$requestHistory->id)
                            ->get(['request_histories.*','A.name as account_name','F.description as fund_name','M.abreviation','users.user_name'])->first();
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
