<?php

namespace App\Http\Controllers;

use App\Models\salaries;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoresalariesRequest;
use App\Http\Requests\UpdatesalariesRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class SalariesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request['user_id']) {
            try {
                $actualuser=User::find($request['user_id']);
                if ($actualuser) {
                    $actualese=$this->getEse($actualuser['id']);
                    if ($actualese) {
                        if ($actualuser['user_type']=="super_admin") {
                            $list=collect(salaries::where('enterprise_id',$actualese['id'])->get());
                            $listdata=$list->map(function ($group){
                                return $this->show($group);
                            });
                            return response()->json([
                                "status"=>200,
                                "message"=>"success",
                                "error"=>null,
                                "data"=>$listdata
                            ]);
                        }else{
                            return response()->json([
                                "status"=>400,
                                "message"=>"error",
                                "error"=>"unauthorized",
                                "data"=>[]
                            ]);
                        }
                    }else{
                        return response()->json([
                            "status"=>400,
                            "message"=>"error",
                            "error"=>"no enterprise find",
                            "data"=>null
                        ]); 
                    }
                }else{
                    return response()->json([
                        "status"=>400,
                        "message"=>"error",
                        "error"=>"no user find",
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
                "error"=>"no user sent",
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
     * @param  \App\Http\Requests\StoresalariesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoresalariesRequest $request)
    {
        $affected=[];
        $failed=[];
        try {
            if ($request['enterprise_id'] && $request['enterprise_id']>0) {
                if ($request['done_by'] && $request['done_by']>0) {
                    if ($request['members'] && count($request['members'])>0) {
                        foreach ($request['members'] as $member) {
                            $ifexists=salaries::where('agent_id',$member['id'])->get()->first();
                            if (!$ifexists) {
                                $newemployee=salaries::create($member);
                                $data=$this->show($newemployee);
                                array_push($affected,$data);
                            }else{
                                array_push($affected,$member);
                            }
                        }

                        return response()->json([
                            "status"=>200,
                            "message"=>"success",
                            "error"=>null,
                            "affected"=>$affected,
                            "failed"=>$failed,
                        ]);
                    }else{
                        return response()->json([
                            "status"=>400,
                            "message"=>"error",
                            "error"=>"no members sent",
                            "data"=>null
                        ]);
                    }
                }else{
                    return response()->json([
                        "status"=>400,
                        "message"=>"error",
                        "error"=>"no user sent",
                        "data"=>null
                    ]);  
                }
            }else{
             return response()->json([
                 "status"=>400,
                 "message"=>"error",
                 "error"=>"no enterprise sent",
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
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\salaries  $salaries
     * @return \Illuminate\Http\Response
     */
    public function show(salaries $salaries)
    {
        $salary=salaries::join('users as A','salaries.agent_id','=','A.id')
        ->join('users as AFB','salaries.affected_by','=','AFB.id')
        ->join('positions as P','salaries.position_id','=','P.id')
        ->where('salaries.id',$salaries->id)
        ->get(['P.participation_rate','P.description as position_description','P.salary_percentage','P.salary_amount','P.method_of_calculation','P.name as position_name','A.uuid as agent_uuid','A.user_phone as agent_phone','A.user_mail as agent_mail','A.user_name as agent_username','A.full_name as agent_fullname','AFB.full_name as done_by_fullname','AFB.user_name as done_by_username','salaries.*'])
        ->first();
      
        return $salary;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\salaries  $salaries
     * @return \Illuminate\Http\Response
     */
    public function edit(salaries $salaries)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatesalariesRequest  $request
     * @param  \App\Models\salaries  $salaries
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatesalariesRequest $request, salaries $salaries)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\salaries  $salaries
     * @return \Illuminate\Http\Response
     */
    public function destroy(salaries $salaries)
    {
        //
    }
}
