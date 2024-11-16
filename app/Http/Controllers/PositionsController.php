<?php

namespace App\Http\Controllers;

use App\Models\positions;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorepositionsRequest;
use App\Http\Requests\UpdatepositionsRequest;
use App\Models\salaries;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class PositionsController extends Controller
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
                            $list=collect(positions::where('enterprise_id',$actualese['id'])->get());
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
                                "status"=>200,
                                "message"=>"success",
                                "error"=>null,
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
     * @param  \App\Http\Requests\StorepositionsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorepositionsRequest $request)
    {
        try {
            if ($request['enterprise_id']) {
                if ($request['done_by']) {
                    if ($request['name']) {
                        $ifexists=positions::where('name',$request['name'])->get()->first();
                        if (!$ifexists) {
                            $newposition=positions::create($request->all());
                         
                            $data=$this->show($newposition);
                            return response()->json([
                                "status"=>200,
                                "message"=>"success",
                                "error"=>null,
                                "data"=>$data
                            ]);
                        }else{
                            return response()->json([
                                "status"=>400,
                                "message"=>"error",
                                "error"=>"duplicated",
                                "data"=>null
                            ]);
                        }
                   }else{
                    return response()->json([
                        "status"=>400,
                        "message"=>"error",
                        "error"=>"no name",
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
     * @param  \App\Models\positions  $positions
     * @return \Illuminate\Http\Response
     */
    public function show(positions $positions)
    {
        $position=positions::join('users','positions.done_by','=','users.id')
        ->where('positions.id',$positions->id)
        ->get(['users.full_name as done_by_fullname','users.user_name as done_by_username','positions.*'])->first();
        if ($position) {
            $position['totalagents']=salaries::where('position_id',$position['id'])->count();
        }else{
            $position['totalagents']=0;
        }

        return $position;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\positions  $positions
     * @return \Illuminate\Http\Response
     */
    public function edit(positions $positions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatepositionsRequest  $request
     * @param  \App\Models\positions  $positions
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatepositionsRequest $request, positions $positions)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\positions  $positions
     * @return \Illuminate\Http\Response
     */
    public function destroy(positions $positions)
    {
        //
    }
}
