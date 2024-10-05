<?php

namespace App\Http\Controllers;

use App\Models\funds;
use Illuminate\Http\Request;
use App\Models\requestHistory;
use App\Http\Requests\StorefundsRequest;
use App\Http\Requests\UpdatefundsRequest;
use App\Models\decision_team;
use Exception;
use Illuminate\Support\Facades\DB;

class FundsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list= funds::leftjoin('users as U', 'funds.user_id','=','U.id')
        ->leftjoin('moneys as M', 'funds.money_id','=','M.id')
        ->get(['M.abreviation as money_abreviation', 'U.user_name', 'funds.*']);
        return $list;
    }

    public function mines($user){
        $list=[];
        $actualuser=$this->getinfosuser($user);
        $ese=$this->getEse($user);
        if ($actualuser) {

            if ($actualuser['user_type']!=='super_admin') {
                $list= funds::leftjoin('users as U', 'funds.user_id','=','U.id')
                ->leftjoin('moneys as M', 'funds.money_id','=','M.id')
                ->where('user_id','=',$user)
                ->get(['M.abreviation as money_abreviation', 'U.user_name', 'funds.*']);
            }
            else{
                $list= funds::leftjoin('users as U', 'funds.user_id','=','U.id')
                ->leftjoin('moneys as M', 'funds.money_id','=','M.id')
                ->where('funds.enterprise_id',$ese->id)
                ->get(['M.abreviation as money_abreviation', 'U.user_name', 'funds.*']);
            }

        }
         
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (isset($request['sold']) &&  $request['sold']>0) {
            # code...
        }else{
            $request['sold']=0;
        }
       
        if(count(funds::all())>0){
            if($request['principal']==true){
                //update others funds
                $this->updatllfundstofalse();
            }
        }else{
            $request['principal']=1;
        }
        
        $fund=funds::create($request->all());
        //make a new entry
        if($fund->sold>0){
            requestHistory::create(['done_at'=>date('Y-m-d'),'user_id'=>$request->created_by,'fund_id'=>$fund->id,'amount'=>$fund->sold,'motif'=>'Premier approvisionnement','type'=>'entry','enterprise_id'=>$request->enterprise_id,'uuid'=>$this->getUuId('C','RH'),'sold'=>$fund->sold]);
        }
       
        return $this->show($fund);
    }

    /**
     * update all principal field funds to false
     */
    public function updatllfundstofalse(){
        DB::update('update funds set principal =0'); 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\funds  $funds
     * @return \Illuminate\Http\Response
     */
    public function show(funds $funds)
    {
        return funds::leftjoin('users as U', 'funds.user_id','=','U.id')
        ->leftjoin('moneys as M', 'funds.money_id','=','M.id')
        ->where('funds.id','=',$funds->id)
        ->get(['M.abreviation as money_abreviation', 'U.user_name', 'funds.*'])[0];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\funds  $funds
     * @return \Illuminate\Http\Response
     */
    public function edit(funds $funds)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatefundsRequest  $request
     * @param  \App\Models\funds  $funds
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatefundsRequest $request, funds $funds)
    {
        $element = funds::find($funds);
        return $element->update($request->all());
    }

    /**
     * Update a specific fund
     */
    public function update2(Request $request,$funds){
        // return $request;
        $element = funds::find($funds);
        if($request['principal']==1){
            $this->updatllfundstofalse();
            $request['principal']=true;
        }
        $element->update($request->all());
        return $this->show($element);
    }

    /**
     * Reset a specific fund
     */
    public function reset(Request $request){
        $requestHistoryCtrl= new RequestHistoryController();
        DB::update('update funds set sold=? where id =? ',[$request['amount'],$request['fund_id']]);
        $tub=funds::find($request['fund_id']);
       
         //archive the operation in request history
         $history = new Request();
         $history['user_id'] = $request['user_id'];
         $history['fund_id'] = $request['fund_id'];
         $history['amount'] =$request['amount'];
         $history['type'] ='entry';
         $history['uuid'] =$this->getUuId('C','RS');
         $history['enterprise_id'] =$this->getEse($request['user_id'])['id'];
         $history['motif'] = 'opening balance';
         $history['done_at'] =date('Y-m-d');
         $mouv_history=requestHistory::create($history->all());

        return ['tub'=>$this->show($tub),'history'=>$requestHistoryCtrl->show($mouv_history)];
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\funds  $funds
     * @return \Illuminate\Http\Response
     */
    public function destroy(funds $funds)
    {
        return funds::destroy($funds);
    }

    /**
     * Remove the specified resource from storage by forcing
     */
    public function destroy2($id){

        $funds=funds::find($id);
       
        $histories=requestHistory::where('fund_id',$funds->id)->get();
        if (count($histories)>0) {
            requestHistory::where('fund_id',$funds->id)->delete();
        } 
        return  funds::find($id)->delete();
    }

    /**
     * getting a specific resource in using the Id
     */
    public function getByid($id) {
        
        $data = funds::find($id);
        if(is_null($data)) {
            return response()->json(['message' => 'Data not found'], 200);
        }
        return response()->json($data::find($id), 200);
    }

    /**
     * request histories by agent
     */
    public function requesthistoriesbyagent(Request $request){
        $listfunds=[];
        if(isset($request->from)==false && empty($request->from) && isset($request->to)==false && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if (isset($request->user_id)) {
            $actualuser=$this->getinfosuser($request->user_id);
            if ($actualuser) {
                $ese=$this->getEse($actualuser->id);
                if ($ese) {
                    if ($actualuser['user_type']!=='super_admin') {
                        $list= funds::leftjoin('users as U', 'funds.user_id','=','U.id')
                        ->leftjoin('moneys as M', 'funds.money_id','=','M.id')
                        ->where('user_id','=',$request->user_id)
                        ->get(['M.abreviation as money_abreviation', 'U.user_name', 'funds.*']);
                        if ($request['funds'] && count($request['funds'])>0) {
                            $listfunds=$request['funds'];
                        }else{
                            $listfunds=$list->pluck('id')->toArray();
                        }
                        
                    }
                    else{
                        $list= funds::leftjoin('users as U', 'funds.user_id','=','U.id')
                        ->leftjoin('moneys as M', 'funds.money_id','=','M.id')
                        ->where('funds.enterprise_id',$ese->id)
                        ->get(['M.abreviation as money_abreviation', 'U.user_name', 'funds.*']);

                        if ($request['funds'] && count($request['funds'])>0) {
                            $listfunds=$request['funds'];
                        }else{
                            $listfunds=$list->pluck('id')->toArray();
                        }
                    }

                    if (count($listfunds)>0) {
                        # get request histories for the funds
                        try {
                            
                            $requestHistoryCtrl = new RequestHistoryController();
                            $histories=collect(requestHistory::whereIn('fund_id',$listfunds)
                            ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                            ->get()); 

                            if($request['accounts'] && count($request['accounts'])>0){
                                $histories=collect(requestHistory::whereIn('fund_id',$listfunds)
                                ->whereIn('account_id',$request['accounts'])
                                ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                                ->get());
                            }
                            
                            if($request['agents'] && count($request['agents'])>0){
                                $histories=collect(requestHistory::whereIn('fund_id',$listfunds)
                                ->whereIn('user_id',$request['agents'])
                                ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                                ->get());
                            }
                           
                            $histories=$histories->transform(function ($item) use($requestHistoryCtrl){
                               return  $requestHistoryCtrl->show($item);
                            });

                            return response()->json([
                                "status"=>200,
                                "message"=>"success",
                                "error"=>null,
                                "data"=>$histories
                            ]);
                        } catch (Exception $th) {
                            return response()->json([
                                "status"=>500,
                                "message"=>"error occured",
                                "error"=>$th->getMessage(),
                                "data"=>null
                            ]);
                        }
                    }
                }else{
                    return response()->json([
                        "status"=>400,
                        "message"=>"unknown enterprise",
                        "error"=>"unknown enterprise",
                        "data"=>null
                    ]);
                }
                
            }else{
                return response()->json([
                    "status"=>400,
                    "message"=>"unknown user",
                    "error"=>"unknown user",
                    "data"=>null
                ]);
            }
        }else{
            return response()->json([
                "status"=>400,
                "message"=>"unknown user",
                "error"=>"unknown user",
                "data"=>null
            ]);
        }
    }

    public function getSold($id){
        $data=funds::find($id);
        return $data['sold'];  
    }

}
