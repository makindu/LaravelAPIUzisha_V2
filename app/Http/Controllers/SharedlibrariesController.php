<?php

namespace App\Http\Controllers;

use App\Models\sharedlibraries;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoresharedlibrariesRequest;
use App\Http\Requests\UpdatesharedlibrariesRequest;
use App\Models\libraries;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class SharedlibrariesController extends Controller
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
     * @param  \App\Http\Requests\StoresharedlibrariesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoresharedlibrariesRequest $request)
    {
        $sentbefore=false;
        $duplicatedsent=[];
        try {
            if ($request['user_id'] && $request['enterprise_id']) {
                $affectation=$this->userenterpriseaffectation($request['user_id'],$request['enterprise_id']);
                if ($affectation) {
                    if ($request['data'] && count($request['data'])>0) {
                        $data=[];
                        foreach ($request['data'] as $key => $library) {
                            $libraryfind=libraries::find($library['id']);
                           
                            if ($libraryfind && $library['users'] && count($library['users'])>0) {
                                foreach ($library['users'] as $key => $user) {
                                    //check if not already sent
                                    $alreadysent=sharedlibraries::where('library',$libraryfind['id'])->where('sharedto',$user['id'])->first();
                                    if (!$alreadysent) {
                                        $inserted=sharedlibraries::create([
                                            'library'=>$libraryfind['id'],
                                            'sharedby'=>$request['user_id'],
                                            'sharedto'=>$user['id'],
                                            'message'=>$request['message']
                                        ]);
                                        array_push($data,$inserted);
                                    }else{
                                        array_push($duplicatedsent,$alreadysent);
                                        $sentbefore=true;
                                    } 
                                }
                            }
                        }
                        return response()->json([
                            "status"=>200,
                            "message"=>"success",
                            "error"=>null,
                            "data"=>$data,
                            "duplication"=>$sentbefore,
                            "alreadysent"=>$duplicatedsent
                        ]);
                    }else{
                        return response()->json([
                            "status"=>400,
                            "message"=>"error",
                            "data"=>null,
                            "error"=>'no data sent'
                        ]);
                    }
                }else{
                    return response()->json([
                        "status"=>400,
                        "message"=>"error",
                        "data"=>null,
                        "error"=>'user not affected'
                    ]);
                }
            }else{
                return response()->json([
                    "status"=>400,
                    "message"=>"error",
                    "data"=>null,
                    "error"=>'incorrects informations about owner'
                ]);   
            }
        } catch (Exception $th) {
            return response()->json([
                "status"=>500,
                "message"=>"error",
                "data"=>null,
                "error"=>$th->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\sharedlibraries  $sharedlibraries
     * @return \Illuminate\Http\Response
     */
    public function show(sharedlibraries $sharedlibraries)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\sharedlibraries  $sharedlibraries
     * @return \Illuminate\Http\Response
     */
    public function edit(sharedlibraries $sharedlibraries)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatesharedlibrariesRequest  $request
     * @param  \App\Models\sharedlibraries  $sharedlibraries
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatesharedlibrariesRequest $request, sharedlibraries $sharedlibraries)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\sharedlibraries  $sharedlibraries
     * @return \Illuminate\Http\Response
     */
    public function destroy(sharedlibraries $sharedlibraries)
    {
        //
    }
}
