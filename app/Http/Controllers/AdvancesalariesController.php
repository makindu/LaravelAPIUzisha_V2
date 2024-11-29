<?php

namespace App\Http\Controllers;

use App\Models\advancesalaries;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreadvancesalariesRequest;
use App\Http\Requests\UpdateadvancesalariesRequest;
use Exception;
use Illuminate\Http\Request;

class AdvancesalariesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (empty($request['from']) && empty($request['to'])) {
            $request['from']=date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        $actualuser=$this->getinfosuser($request['agent_id']);
        if ($actualuser && $actualuser['user_type']=="super_admin") {
            if ($request['agents'] && count($request['agents'])>0) {
                $list=collect(advancesalaries::whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->whereIn('agent_id',$request['agents'])
                ->get());
            }else{
                $list=collect(advancesalaries::whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->get());
            }

            $list=$list->transform(function ($item){
                return $this->show($item);
            });
        }else{
            $list=collect(advancesalaries::whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->where('agent_id',$request['agent_id'])->get());
            $list=$list->transform(function ($item){
                return $this->show($item);
            });
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
     * @param  \App\Http\Requests\StoreadvancesalariesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreadvancesalariesRequest $request)
    {
        if ($request['enterprise_id']) {
            if ($request['agent_id']) {
                if ($request['money_id']) {
                    try {
                        $request['uuid']=$this->getUuId('Weka',"AVS");
                        $newadvance=advancesalaries::create($request->all());
                        if ($newadvance) {
                            return response()->json([
                                "status"=>200,
                                "message"=>"success",
                                "error"=>null,
                                "data"=>$this->show($newadvance)
                            ]);
                        }else{
                            return response()->json([
                                "status"=>400,
                                "message"=>"error",
                                "error"=>"impossible to save",
                                "data"=>null
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
                    return response()->json([
                        "status"=>400,
                        "message"=>"error",
                        "error"=>"money not sent",
                        "data"=>null
                    ]); 
                }
            }else{
                return response()->json([
                    "status"=>400,
                    "message"=>"error",
                    "error"=>"agent not sent",
                    "data"=>null
                ]); 
            }
        }else{
            return response()->json([
                "status"=>400,
                "message"=>"error",
                "error"=>"enterprise not sent",
                "data"=>null
            ]); 
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\advancesalaries  $advancesalaries
     * @return \Illuminate\Http\Response
     */
    public function show(advancesalaries $advancesalaries)
    {
        return advancesalaries::join('users','advancesalaries.done_by_id','=','users.id')
        ->join('users as MU','advancesalaries.agent_id','MU.id')
        ->join('moneys as M','advancesalaries.money_id','M.id')
        ->where('advancesalaries.id','=',$advancesalaries->id)
        ->get(['MU.user_name as member_user_name','MU.full_name as member_fullname','MU.uuid as member_uuid',
        'advancesalaries.*',
        'M.abreviation','M.money_name',
        'users.user_name as done_by_name','users.full_name as done_by_fullname','users.uuid as done_by_uuid'])
        ->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\advancesalaries  $advancesalaries
     * @return \Illuminate\Http\Response
     */
    public function edit(advancesalaries $advancesalaries)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateadvancesalariesRequest  $request
     * @param  \App\Models\advancesalaries  $advancesalaries
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateadvancesalariesRequest $request, advancesalaries $advancesalaries)
    {
        if ($request['mode'] && $request['mode']=="multiple") {
           return  $this->multipleupdating($request);
        }else{
            if ($request['enterprise_id']) {
                if ($request['agent_id']) {
                    if ($request['money_id']) {
                        try {
                            $find=advancesalaries::find($request['id']);
                            if ($find) {
                                try {
                                    $find->update([
                                        'amount'=>$request['amount'],
                                        'description'=>$request['description'],
                                        'money_id'=>$request['money_id'],
                                        'done_at'=>$request['done_at'],
                                        'status'=>$request['status']
                                    ]);
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
                                    "error"=>"impossible to save",
                                    "data"=>null
                                ]); 
                            }
                           
                            if ($newadvance) {
                                return response()->json([
                                    "status"=>200,
                                    "message"=>"success",
                                    "error"=>null,
                                    "data"=>$this->show($newadvance)
                                ]);
                            }else{
                                return response()->json([
                                    "status"=>400,
                                    "message"=>"error",
                                    "error"=>"impossible to save",
                                    "data"=>null
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
                        return response()->json([
                            "status"=>400,
                            "message"=>"error",
                            "error"=>"money not sent",
                            "data"=>null
                        ]); 
                    }
                }else{
                    return response()->json([
                        "status"=>400,
                        "message"=>"error",
                        "error"=>"agent not sent",
                        "data"=>null
                    ]); 
                }
            }else{
                return response()->json([
                    "status"=>400,
                    "message"=>"error",
                    "error"=>"enterprise not sent",
                    "data"=>null
                ]); 
            }
        }
    }

    public function deleteadvance(Request $request){
        if ($request['enterprise_id']) {
            if ($request['agent_id']) {
                if ($request['money_id']) {
                    try {
                        $find=advancesalaries::find($request['id']);
                        if ($find) {
                            try {
                                $find->update([
                                    'amount'=>$request['amount'],
                                    'description'=>$request['description'],
                                    'money_id'=>$request['money_id'],
                                    'done_at'=>$request['done_at'],
                                    'status'=>'cancelled'
                                ]);
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
                                "error"=>"impossible to save",
                                "data"=>null
                            ]); 
                        }
                       
                        if ($newadvance) {
                            return response()->json([
                                "status"=>200,
                                "message"=>"success",
                                "error"=>null,
                                "data"=>$this->show($newadvance)
                            ]);
                        }else{
                            return response()->json([
                                "status"=>400,
                                "message"=>"error",
                                "error"=>"impossible to save",
                                "data"=>null
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
                    return response()->json([
                        "status"=>400,
                        "message"=>"error",
                        "error"=>"money not sent",
                        "data"=>null
                    ]); 
                }
            }else{
                return response()->json([
                    "status"=>400,
                    "message"=>"error",
                    "error"=>"agent not sent",
                    "data"=>null
                ]); 
            }
        }else{
            return response()->json([
                "status"=>400,
                "message"=>"error",
                "error"=>"enterprise not sent",
                "data"=>null
            ]); 
        }
    }

    /**
     * multiple updating
     */
    public function multipleupdating(Request $request){
        // return $request;
        $updated=[];
        $failed=[];
        if ($request['done_by']) {
            if ($request['advances'] && count($request['advances'])>0) {
                if ($request['criteria']) {
                    foreach ($request['advances'] as $advance) {
                        $find=advancesalaries::find($advance['id']);
                        if ($find) {
                            $find->update([
                                'amount'=>$advance['amount'],
                                'description'=>$advance['description'],
                                'money_id'=>$advance['money_id'],
                                'done_at'=>$advance['done_at'],
                                'status'=>$request['criteria']
                            ]);
                            array_push($updated,$this->show($find));
                        }else{
                            array_push($failed,$this->show($find));
                        }
                    }
                    return response()->json([
                        "status"=>200,
                        "message"=>"success",
                        "updated"=>$updated,
                        "failed"=>$failed
                    ]);
                }else{
                    //no criteria find
                    return response()->json([
                        "status"=>400,
                        "message"=>"error",
                        "error"=>"no criteria sent",
                        "data"=>null
                    ]); 
                }
            }else{
                //no advances sent
                return response()->json([
                    "status"=>400,
                    "message"=>"error",
                    "error"=>"no advances sent",
                    "data"=>null
                ]); 
            }
        }else{
            //no user sent
            return response()->json([
                "status"=>400,
                "message"=>"error",
                "error"=>"agent not sent",
                "data"=>null
            ]); 
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\advancesalaries  $advancesalaries
     * @return \Illuminate\Http\Response
     */
    public function destroy(advancesalaries $advancesalaries)
    {
        //
    }
}
