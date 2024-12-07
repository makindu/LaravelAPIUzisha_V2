<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\wekagroups;
use Illuminate\Http\Request;
use App\Models\wekagroupmembers;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorewekagroupsRequest;
use App\Http\Requests\UpdatewekagroupsRequest;

class WekagroupsController extends Controller
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
                            $list=collect(wekagroups::where('enterprise_id',$actualese['id'])->get());
                            $listdata=$list->map(function ($group){
                                return $this->show($group);
                            });
                            return response()->json([
                                "status"=>200,
                                "message"=>"success",
                                "error"=>null,
                                "data"=>$listdata
                            ]);
                        }else if($actualuser['collector']){
                            $list=collect(wekagroups::join('wekagroupmembers','wekagroups.id','=','wekagroupmembers.group_id')
                                    ->where('wekagroupmembers.member_id',$actualese['id'])
                                    ->where('wekagroupmembers.level','admin')->get(['wekagroups']));
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
                            $list=collect(wekagroups::where('enterprise_id',$actualese['id'])->get());
                            $listdata=$list->map(function ($group){
                                return $this->show($group);
                            });
                            return response()->json([
                                "status"=>200,
                                "message"=>"success",
                                "error"=>null,
                                "data"=>$listdata
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
     * Get groups with members at the same time
     */
    public function groupandmembers(Request $request){
        $list=collect(wekagroups::where('enterprise_id',$request['enterprise_id'])->get());
        $listdata=$list->map(function ($group){
            $group=$this->show($group);
            $group['members']=$this->getmembers($group['id']);
            return $group;
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
     * @param  \App\Http\Requests\StorewekagroupsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorewekagroupsRequest $request)
    {
        try {
           if ($request['name']) {
                $ifexists=wekagroups::where('name',$request['name'])->get()->first();
                if (!$ifexists) {
                    $newgroup=wekagroups::create($request->all());
                    if ($request['members'] && count($request['members'])>0) {
                        //affect members
                        foreach ($request['members'] as $member) {
                            $alreadyaffected=wekagroupmembers::where('member_id',$member['id'])->first();
                            if (!$alreadyaffected) {
                               wekagroupmembers::create([
                                'level'=>'member',
                                'done_by_id'=>$newgroup['done_by'],
                                'member_id'=>$member['id'],
                                'group_id'=>$newgroup['id']
                               ]); 
                            }
                        }
                    }
                    $data=$this->show($newgroup);
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
                        "error"=>"name duplicated",
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
     * @param  \App\Models\wekagroups  $wekagroups
     * @return \Illuminate\Http\Response
     */
    public function show(wekagroups $wekagroups)
    {
        $group=wekagroups::join('users','wekagroups.done_by','=','users.id')
                ->where('wekagroups.id',$wekagroups->id)
                ->get(['wekagroups.*','users.full_name','users.user_name'])->first();
        if ($group) {
            $group['totalmembers']=wekagroupmembers::where('group_id',$group['id'])->count();
        }else{
            $group['totalmembers']=0;
        }

        return $group;
    } 
    
    public function showmember(wekagroupmembers $wekagroups)
    {
        $userctrl = new UsersController();
        $affectation=wekagroupmembers::find($wekagroups->id);
        $member=$userctrl->show(User::find($wekagroups->member_id));
        $member['level']= $affectation->level;
        $member['group_id']= $affectation->group_id;
        $member['affected_at']= $affectation->created_at;
        return $member;
    }

    public function getmembers($wekagroups){
       
        $members=collect(wekagroupmembers::where('group_id',$wekagroups)->get());

        $list=$members->map(function ($member){
            return $this->showmember($member);
        });
        $grouped=$list->sortBy('level');
        return $grouped->values()->all();
    }

    public function addmembers(Request $request){
        try {
            if ($request['group_id']) 
            {
                 $ifexists=wekagroups::where('id',$request['group_id'])->get()->first();
                 if ($ifexists) {
                     if ($request['members'] && count($request['members'])>0) {
                         //affect members
                         foreach ($request['members'] as $member) {
                             $alreadyaffected=wekagroupmembers::where('member_id',$member['id'])->first();
                             if (!$alreadyaffected) {
                                wekagroupmembers::create([
                                 'level'=>'member',
                                 'done_by_id'=>$ifexists['done_by'],
                                 'member_id'=>$member['id'],
                                 'group_id'=>$ifexists['id']
                                ]); 
                             }
                         }
                     }
                     $data=$this->getmembers($ifexists['id']);
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
                         "error"=>"no group find",
                         "data"=>null
                     ]);
                 }
            }else{
             return response()->json([
                 "status"=>400,
                 "message"=>"error",
                 "error"=>"no group sent",
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

   public function removemembers(Request $request){
    $deleted=0;
        try {
            $members=wekagroupmembers::where('group_id',$request['group_id'])
            ->whereIn('member_id',$request['members'])
            ->get();

            foreach ($members as $affectation) {
                if ($affectation->delete()) {
                    $deleted++;
                }
            }
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
    
    public function memberslevel(Request $request){
    
        try {
            $member=wekagroupmembers::where('group_id',$request['group_id'])
            ->where('member_id',$request['member_id'])
            ->first();
            if ($member) {
                if ($request['level']=="member" || $request['level']=="admin") {
                    if ($request['level']=="admin") {
                        $user=User::find($request['member_id']);
                        if ($user && $user['collector']) {
                            DB::update('update wekagroupmembers set level = ?  where group_id = ?',['member',$request['group_id']]);
                            $member->update(['level'=>$request['level']]);
                        }
                        
                    }else{
                        $member->update(['level'=>$request['level']]);
                    }

                  
                    return response()->json([
                        "status"=>200,
                        "message"=>"success",
                        "error"=>null,
                        "data"=>$this->showmember($member)
                    ]);  
                }else{
                    return response()->json([
                        "status"=>400,
                        "message"=>"error",
                        "error"=>"unknown level",
                        "data"=>null
                    ]); 
                } 
            }else{

                return response()->json([
                    "status"=>400,
                    "message"=>"error",
                    "error"=>"affectation not find",
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

    public function memberslookup(Request $request){

        if($request->keyword && !empty($request->keyword)){
            $list=collect(wekagroupmembers::join('users','wekagroupmembers.member_id','users.id')
            ->where(function($query) use($request){
                $query->where('wekagroupmembers.group_id','=',$request['group_id'])
                    ->where('users.full_name','LIKE',"%$request->keyword%");      
            })->orWhere(function($query) use($request){
                $query->where('wekagroupmembers.group_id','=',$request['group_id'])
                ->where('users.uuid','LIKE',"%$request->keyword%");
            })->orWhere(function($query) use($request){
                $query->where('wekagroupmembers.group_id','=',$request['group_id'])
                ->where('users.user_name','LIKE',"%$request->keyword%");
            }) 
            ->limit(10)
            ->get('wekagroupmembers.*'));
        
            $listdata=$list->map(function ($item){
                return $this->showmember($item);
            });
            return $listdata;
        }else{
            return [];
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\wekagroups  $wekagroups
     * @return \Illuminate\Http\Response
     */
    public function edit(wekagroups $wekagroups)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatewekagroupsRequest  $request
     * @param  \App\Models\wekagroups  $wekagroups
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatewekagroupsRequest $request, wekagroups $wekagroups)
    {
        $group=wekagroups::find($request['id']);
        if ($group) {
           $group->update(['name'=>$request['name'],'description'=>$request['description']]);
        }
        return  $this->show($group);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\wekagroups  $wekagroups
     * @return \Illuminate\Http\Response
     */
    public function destroy(wekagroups $wekagroups)
    {
        //
    }
}
