<?php

namespace App\Http\Controllers;

use App\Models\salaries;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoresalariesRequest;
use App\Http\Requests\UpdatesalariesRequest;
use App\Models\advancesalaries;
use App\Models\InvoiceDetails;
use App\Models\moneys;
use App\Models\User;
use App\Models\wekafirstentries;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * deleting salary
     */
    public function deletesalary(Request $request){
        $salary=salaries::find($request['id']);
        if ($salary) {
            try {
                $deleted=$salary->delete();
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

    private function notebooksbyagent(Request $request){
        $mouvements=InvoiceDetails::join('invoices as I','invoice_details.invoice_id','=','I.id')
                                ->whereBetween('I.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                                ->where('I.collector_id','=',$request['agent_id'])
                                ->where('I.type_facture','<>','proforma')
                                ->get(['invoice_details.*']);
        return $mouvements;
    } 
    
    private function firstentriesbyagent(Request $request){
        $moneyslist=[];
        if ($request['enterprise_id']) {
            $agent=User::find($request['agent']['agent_id']);
            $employee=$this->show(salaries::find($request['agent']['id']));
            $moneys=collect(moneys::where('enterprise_id',$request['enterprise_id'])->get());
            $moneyslist=$moneys->transform(function ($money) use($request,$agent,$employee){
                $moneyinfos=moneys::find($money['id'],['id','abreviation','money_name']);
                if ($agent['collector']) {
                    $sumfirstentries=wekafirstentries::select(DB::raw('sum(amount) as total'))
                                               ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                                               ->where('money_id','=',$moneyinfos['id'])
                                               ->where('collector_id',$agent['id'])
                                               ->first();
                    $moneyinfos['firstentriestotal']=($sumfirstentries['total']*$employee['participation_rate'])/100;
                    
                }else{
                    $sumfirstentries=wekafirstentries::select(DB::raw('sum(amount) as total'))
                                               ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                                               ->where('money_id','=',$moneyinfos['id'])
                                               ->first();
                    $moneyinfos['firstentriestotal']=(($sumfirstentries['total']*$employee['participation_rate'])/100)*$employee['salary_percentage']/100;
                }
             
              return $moneyinfos;
            });
        }
        
        return $moneyslist;
    }
    
    private function advancesSalariesbyagent(Request $request){
        $moneyslist=[];
        if ($request['enterprise_id']) {
            $agent=User::find($request['agent']['agent_id']);
            $moneys=collect(moneys::where('enterprise_id',$request['enterprise_id'])->get());
            $moneyslist=$moneys->transform(function ($money) use($request,$agent){
                $moneyinfos=moneys::find($money['id'],['id','abreviation','money_name']);
               
                    $sumadvances=advancesalaries::select(DB::raw('sum(amount) as total'))
                        ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                        ->where('money_id','=',$moneyinfos['id'])
                        ->where('agent_id',$agent['id'])
                        ->where('status','validated')
                        ->first();
                    $moneyinfos['advancestotal']=$sumadvances['total']?$sumadvances['total']:0;
             
              return $moneyinfos;
            });
        }
        
        return $moneyslist;
    }

    private function grossSalariesCalculation(Request $request){
        $moneyslist=[];
        $moneys=collect(moneys::where('enterprise_id',$request['enterprise_id'])->get());
        $moneyslist=$moneys->transform(function ($money) use($request){
            $moneyinfos=moneys::find($money['id'],['id','abreviation','money_name','principal']);
                $moneyinfos['totalgeneral']=0;
                if ($moneyinfos['principal']==1) {
                    $moneyinfos['totalgeneral']=$moneyinfos['totalgeneral']+$request['remunerationNotebooks'];
                }
                $moneyinfos['totalgeneral']= $moneyinfos['totalgeneral']+($request['firstentries']->where('id','=', $moneyinfos['id'])->sum('firstentriestotal'));
                $moneyinfos['totalgeneral']= $moneyinfos['totalgeneral']-($request['salaryadvances']->where('id','=', $moneyinfos['id'])->sum('advancestotal'));

          return $moneyinfos;
        });

        return $moneyslist;
    }

    /**
     * Get salaries of agents
     */
    public function employeesalairies(Request $request){
        // return $request;
        if (empty($request['from']) && empty($request['to'])) {
            $request['from']=date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        $list=collect(salaries::where('enterprise_id',$request['enterprise_id'])->get());
        $listdata=$list->map(function ($agent) use($request){
            $agent=$this->show($agent);

            $notebooksSold=$this->notebooksbyagent(new Request(["agent_id"=>$agent['agent_id'],"from"=>$request['from'],"to"=>$request['to']]));
            $firstentries=$this->firstentriesbyagent(new Request(["enterprise_id"=>$request['enterprise_id'],"agent"=>$agent,"from"=>$request['from'],"to"=>$request['to']]));
            $salaryadvances=$this->advancesSalariesbyagent(new Request(["enterprise_id"=>$request['enterprise_id'],"agent"=>$agent,"from"=>$request['from'],"to"=>$request['to']]));
            $grossSalaryies=$this->grossSalariesCalculation(new Request(["salaryadvances"=>$salaryadvances,"enterprise_id"=>$request['enterprise_id'],'firstentries'=>$firstentries,'remunerationNotebooks'=>$notebooksSold->sum('total')]));

            $agent['notebooksSold']=$notebooksSold->sum('quantity');
            $agent['remunerationNotebooks']=$notebooksSold->sum('total');
            $agent['firstentries']=$firstentries;

            $agent['grossSalaryies']=$grossSalaryies;

            $agent['overtime']=0;
            $agent['premiums']=0;
            $agent['salaryadvances']=$salaryadvances;
            $agent['NetCompensation']=0;
            return $agent;
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
        // return $request;
        try {
            $find=salaries::find($request['id']);
            if ($find) {
                 $find->update(['position_id'=>$request['position_id'],'description'=>$request['description']]);
                 return response()->json([
                    "status"=>200,
                    "message"=>"success",
                    "error"=>null,
                    "data"=>$this->show($find)
                ]);
            }else{
                return response()->json([
                    "status"=>400,
                    "message"=>"error",
                    "error"=>"no employee find",
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
