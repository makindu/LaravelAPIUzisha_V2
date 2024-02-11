<?php

namespace App\Http\Controllers;

use App\Models\request_served;
use App\Http\Requests\Storerequest_servedRequest;
use App\Http\Requests\Updaterequest_servedRequest;
use App\Models\funds;
use App\Models\requestHistory;
use App\Models\requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class RequestServedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list=collect(request_served::all());
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $msg=null;
        $requestServed=null;
        $requisition=requests::find($request->request_id);
        $fund=funds::find($request['fund_id']);
        //if it exists request?
        if(is_null($requisition)){
            $msg='request_not_founded';
        }else{
             //if it exists fund?
             if(is_null($fund)){
                //fund does'nt exist
                $msg='tub_not_founded';
            }else{
                //test sold via amount to withdraw
                $soldeFund = $fund->sold;
                if($soldeFund>=$request['amount']){
                     //request can be served
                     $canbeserved=$this->canbeserved($request['request_id']);
                     if($canbeserved=='partially_served'){
                        //check if the amount is not superior than total request
                        if($requisition['total']<$request['amount']){
                            //take the sold to pay
                            $request['amount']=$this->soldpaymentforarequest($request['request_id']);
                        }
                        //serve the request
                        $newoperation=request_served::create($request->all());
                        $requestServed=$this->show(request_served::find($newoperation->id));
                        //update fund
                        $caisee = new request();
                        $caisee['sold'] = ($fund['sold']-$request['amount']);
                        $fund->update($caisee->all());
    
                        //archive the operation in request history
                        $history = new Request();
                        $history['user_id'] = $request['served_by'];
                        $history['request_id'] = $request['request_id'];
                        $history['fund_id'] = $request['fund_id'];
                        $history['amount'] =$request['amount'];
                        $history['type'] ='withdraw';
                        if(isset($request->motif)&& !empty($request->motif)){
                            $history['motif'] =$request->motif;
                        }else{
                            $history['motif'] = 'décaissement réquisitiion';
                        }

                        requestHistory::create($history->all());
                      
                        //update request status
                        $this->isRequestfinished($request['request_id']);
                        $msg='successful';
                    }else{
                        $msg='already_served';
                    }
                }else{
                    $msg='negative_sold';
                }
            }
        }
        
        return ['request_status'=>$this->canbeserved($request['request_id']),'disbursement'=>$requestServed,'message'=>$msg];
    }

    private function isRequestfinished($requestId){

        $sumalreadyserved =request_served ::select(DB::raw('sum(amount) as somme'))->where('request_id','=',$requestId)->get('somme');
        $totalamountrequest=requests::where('id','=',$requestId)->get('total')[0];

        if($totalamountrequest['total'] <=$sumalreadyserved[0]->somme){
            $this->updaterequeststatus($requestId,'served');
        }else{
            $this->updaterequeststatus($requestId,'partially_served');  
        }
    }

    private function canbeserved($requestId){

        $sumalreadyserved =request_served ::select(DB::raw('sum(amount) as somme'))->where('request_id','=',$requestId)->get('somme');
        $totalamountrequest=requests::where('id','=',$requestId)->get('total')[0];

        if($totalamountrequest['total'] <=$sumalreadyserved[0]->somme){
            $msg='served';
        }else{
            $msg='partially_served';  
        }

        return $msg;
    }

    private function soldpaymentforarequest($requestId){
        $sumalreadyserved =request_served ::select(DB::raw('sum(amount) as somme'))->where('request_id','=',$requestId)->get('somme');
        if(is_null($sumalreadyserved)){
            $sum=0;
        }else{
            $sum=$sumalreadyserved[0]->somme;
        }
        return $sum;
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\request_served  $request_served
     * @return \Illuminate\Http\Response
     */
    public function show(request_served $request_served)
    {
        return request_served::leftjoin('users as U','request_serveds.served_by','=','U.id')
        ->leftjoin('funds as F','request_serveds.fund_id','=','F.id')
        ->leftjoin('moneys as M','F.money_id','=','M.id')
        ->where('request_serveds.id','=',$request_served->id)
        ->get(['F.description as fund_description','M.abreviation as money_abreviation','U.user_name','request_serveds.*'])[0];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\request_served  $request_served
     * @return \Illuminate\Http\Response
     */
    public function edit(request_served $request_served)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Updaterequest_servedRequest  $request
     * @param  \App\Models\request_served  $request_served
     * @return \Illuminate\Http\Response
     */
    public function update(Updaterequest_servedRequest $request, request_served $request_served)
    {
        $element = request_served::find($request_served);
        return $element->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\request_served  $request_served
     * @return \Illuminate\Http\Response
     */
    public function destroy(request_served $request_served)
    {
        return request_served::destroy($request_served);
    }

    public function getrequest_servedByIdRequest($id) {
        // $this->C.created_at->format("Y-m-d");
        $data =request_served::where('request_id', '=', $id)
        ->join('moneys as M', 'request_serveds.money_id','=','M.id')
        ->get(['M.abreviation as money_abreviation','request_serveds.*']);
        return response()->json($data);
    }

}
