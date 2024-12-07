<?php

namespace App\Http\Controllers;

use App\Models\wekafirstentries;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorewekafirstentriesRequest;
use App\Http\Requests\UpdatewekafirstentriesRequest;
use App\Models\moneys;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WekafirstentriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // return $request;
        $list=[];
        if(isset($request->from)==false && empty($request->from) && isset($request->to)==false && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if (isset($request->user_id)) {
            $actualuser=$this->getinfosuser($request->user_id);
            if ($actualuser) {
                $ese=$this->getEse($actualuser->id);
                if ($ese) {
                    if ($actualuser['user_type']=='super_admin') {
                        //report for super admin users
                        try {
                            if (isset($request['members']) && count($request['members'])>0) {

                                $list1=collect(wekafirstentries::whereIn('member_id',$request['members'])
                                ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                                ->get());
                                $list=$list1->transform(function($item){
                                    return $this->show($item);
                                });
                    
                                return response()->json([
                                    "status"=>200,
                                    "message"=>"success",
                                    "error"=>null,
                                    "data"=>$list
                                ]);
                            }   
                            elseif (isset($request['collectors']) && count($request['collectors'])>0) {
                                $list1=collect(wekafirstentries::whereIn('collector_id',$request['collectors'])
                                ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                                ->get());
                                $list=$list1->transform(function($item){
                                    return $this->show($item);
                                });
                    
                                return response()->json([
                                    "status"=>200,
                                    "message"=>"success",
                                    "error"=>null,
                                    "data"=>$list
                                ]);
                            }elseif (isset($request['moneys']) && count($request['moneys'])>0) {

                                $list1=collect(wekafirstentries::whereIn('money_id',$request['moneys'])
                                ->where('enterprise_id',$ese->id)
                                ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                                ->get());
                                $list=$list1->transform(function($item){
                                    return $this->show($item);
                                });
                    
                                return response()->json([
                                    "status"=>200,
                                    "message"=>"success",
                                    "error"=>null,
                                    "data"=>$list
                                ]);
                            }else{
                                if (isset($request['groupby'])) {
                                    switch ($request['groupby']) {
                                        case 'collectors':
                                            return $this->wekafirstentriesgroupedbycollectors($request);
                                            break;
                                        
                                        default:
                                            # code...
                                            break;
                                    }
                                }
                                $list1=collect(wekafirstentries::where('enterprise_id',$request['enterprise_id'])
                                ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                                ->get());
                                $list=$list1->transform(function($item){
                                    return $this->show($item);
                                });
                    
                                return response()->json([
                                    "status"=>200,
                                    "message"=>"success",
                                    "error"=>null,
                                    "data"=>$list
                                ]);
                            }

                            
                        } catch (Exception $th) {
                            return response()->json([
                                "status"=>500,
                                "message"=>"error",
                                "error"=>$th->getMessage(),
                                "data"=>null
                            ]);
                        }
                    }else{
                        //report for no super admin users
                    }
                }else{
                    return response()->json([
                        "status"=>400,
                        "message"=>"error",
                        "error"=>"unknown enterprise",
                        "data"=>null
                    ]);
                }

            }else{
                return response()->json([
                    "status"=>400,
                    "message"=>"error",
                    "error"=>"unknown user",
                    "data"=>null
                ]);
            }
        }
        else{
            return response()->json([
                "status"=>400,
                "message"=>"error",
                "error"=>"user not sent",
                "data"=>null
            ]);
        }
    }

    /**
     * wekafirstentries groupby collectors
     */
    public function wekafirstentriesgroupedbycollectors(Request $request){
        try {
            $esemoneys=collect(moneys::where('enterprise_id',$request['enterprise_id'])->get());
            $esemoneys=$esemoneys->transform(function ($money) use($request){
                $moneyinfos=moneys::find($money['id'],['id','abreviation','money_name']);
                $sumfirstentries=wekafirstentries::select(DB::raw('sum(amount) as total'))
                ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->where('enterprise_id',$request['enterprise_id'])
                ->where('money_id','=',$money['id'])
                ->first();
                $moneyinfos['total']=$sumfirstentries['total'];
                return $moneyinfos;
            });

            $list1=collect(wekafirstentries::where('enterprise_id',$request['enterprise_id'])
            ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->select('collector_id')
            ->groupBy('collector_id')
            ->get());
        
            $collectors=$list1->transform(function($item) use($request){
                $collector=User::find($item['collector_id'],['id','full_name','user_name','avatar']);
                
                $moneys=collect(wekafirstentries::where('enterprise_id',$request['enterprise_id'])
                ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->where('collector_id',$item['collector_id'])
                ->select('money_id')
                ->groupBy('money_id')
                ->get());

                $moneysdetails=$moneys->transform(function($money) use($request,$item){
                    $moneyinfos=moneys::find($money['money_id'],['id','abreviation','money_name']);
                    $sumfirstentries=wekafirstentries::select(DB::raw('sum(amount) as total'))
                                               ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                                               ->where('money_id','=',$money['money_id'])
                                               ->where('collector_id',$item['collector_id'])
                                               ->first();
                    $moneyinfos['total']=$sumfirstentries['total'];
                    return $moneyinfos;
                });

                $collector['moneys']=$moneysdetails;
                return $collector;
            });
            return response()->json([
                "status"=>200,
                "message"=>"success",
                "error"=>null,
                "cumul"=>$esemoneys,
                "data"=>$collectors
            ]);
        } catch (Exception $th) {
            return response()->json([
                "status"=>500,
                "message"=>"error",
                "error"=>$th->getMessage(),
                "data"=>null
            ]);
        }
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
     * @param  \App\Http\Requests\StorewekafirstentriesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorewekafirstentriesRequest $request)
    {
        // return $request;
        if ($request['member_id']) {
            //looking for the account
            $member=$this->getinfosuser($request['member_id']);
            if ($member) {
                $actualEse=$this->getEse($request['member_id']);
                if ($actualEse) {
                    $newfirstentry= wekafirstentries::create([
                        'amount'=>$request['amount'],
                        'description'=>$request['description'],
                        'done_by_id'=>$request['done_by_id'],
                        'member_id'=>$member['id'],
                        'collector_id'=>$request['collector_id'],
                        'money_id'=>$request['money_id'],
                        'enterprise_id'=>$actualEse['id'],
                        'done_at'=>$request['done_at'],
                        'sync_status'=>1,
                        'uuid'=>$this->getUuId('WEKA','FE'),
                        'cashed'=>$request['cashed'],
                        'cashed_by'=>$request['cashed_by'],
                        'cashed_at'=>$request['cashed_at'],
                        'fund'=>$request['fund']
                    ]); 

                    return response()->json([
                        "status"=>200,
                        "message"=>"success",
                        "error"=>null,
                        "data"=>$this->show($newfirstentry)
                    ]);
                  
                }else{
                    return response()->json([
                        "status"=>400,
                        "message"=>"error",
                        "error"=>"enterprise unknown",
                        "data"=>null
                    ]);
                }  
            }else{
                return response()->json([
                    "status"=>401,
                    "message"=>"error",
                    "error"=>"no member find",
                    "data"=>null
                ]); 
            }
        }else{
            return response()->json([
                "status"=>400,
                "message"=>"error",
                "error"=>"no member sent",
                "data"=>null
            ]);  
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\wekafirstentries  $wekafirstentries
     * @return \Illuminate\Http\Response
     */
    public function show(wekafirstentries $wekafirstentries)
    {
        return wekafirstentries::join('users','wekafirstentries.done_by_id','=','users.id')
        ->join('users as MU','wekafirstentries.member_id','MU.id')
        ->join('moneys as M','wekafirstentries.money_id','M.id')
        ->join('users as CU','wekafirstentries.collector_id','CU.id')
        ->leftjoin('funds as F','wekafirstentries.fund','F.id')
        ->where('wekafirstentries.id','=',$wekafirstentries->id)
        ->get(['MU.user_name as member_user_name','MU.full_name as member_fullname','MU.uuid as member_uuid',
        'wekafirstentries.*',
        'CU.user_name as collector_user_name','CU.full_name as collector_fullname','CU.uuid as collector_uuid',
        'M.abreviation','M.money_name',
        'F.description as fund_description',
        'users.user_name as done_by_name','users.full_name as done_by_fullname','users.uuid as done_by_uuid'])
        ->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\wekafirstentries  $wekafirstentries
     * @return \Illuminate\Http\Response
     */
    public function edit(wekafirstentries $wekafirstentries)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatewekafirstentriesRequest  $request
     * @param  \App\Models\wekafirstentries  $wekafirstentries
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatewekafirstentriesRequest $request, wekafirstentries $wekafirstentries)
    {
        $find=wekafirstentries::find($request['id']);
        if ($find) {
            try {
                $updated=$find->update([
                    'amount'=>$request['amount'],
                    'description'=>$request['description'],
                    'done_by_id'=>$request['done_by_id'],
                    'member_id'=>$request['member_id'],
                    'collector_id'=>$request['collector_id'],
                    'money_id'=>$request['money_id'],
                    'sync_status'=>$request['sync_status'],
                    'cashed'=>$request['cashed'],
                    'cashed_by'=>$request['cashed_by'],
                    'fund'=>$request['fund'],
                    'enterprise_id'=>$request['enterprise_id'],
                    'done_at'=>$request['done_at']
                ]);
                if (!$updated) {
                    return response()->json([
                        "status"=>400,
                        "message"=>"error",
                        "error"=>'enable to achieve action',
                        "data"=>$this->show($find) 
                    ]);
                }
                return response()->json([
                    "status"=>200,
                    "message"=>"success",
                    "error"=>null,
                    "data"=>$this->show($find) 
                ]);
            } catch (Exception $th) {
                return response()->json([
                    "status"=>500,
                    "message"=>"error",
                    "error"=>$th->getMessage(),
                    "data"=>null
                ]); 
            }
        }else{
            return response()->json([
                "status"=>400,
                "message"=>"error",
                "error"=>"entry not find",
                "data"=>null
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\wekafirstentries  $wekafirstentries
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $wekafirstentries)
    {
        $find=wekafirstentries::find($wekafirstentries['id']);
        if ($find) {
            try {
                $deleted=$find->delete();
                return response()->json([
                    "status"=>200,
                    "message"=>"success",
                    "error"=>null,
                    "data"=>$deleted
                ]);
            } catch (Exception $th) {
                return response()->json([
                    "status"=>500,
                    "message"=>"error",
                    "error"=>$th->getMessage(),
                    "data"=>null
                ]); 
            }
           
        }
    }
}
