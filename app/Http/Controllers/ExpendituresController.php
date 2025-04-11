<?php

namespace App\Http\Controllers;

use stdClass;
use Exception;
use App\Models\Expenditures;
use Illuminate\Http\Request;
use App\Models\UsersExpendituresLimits;
use App\Http\Requests\StoreExpendituresRequest;
use App\Http\Requests\UpdateExpendituresRequest;

class ExpendituresController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseid)
    {
        $list=collect(Expenditures::where('enterprise_id','=',$enterpriseid)->get());
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $listdata;
    }

    /**
     * get expenditure by Id
     */
    public function getexpenditurebyid($expenditureId){
        try{
            return response()->json([
                "message"=>"success",
                "status"=>200,
                "error"=>null,
                "data"=>$this->show(Expenditures::find($expenditureId))
            ]);
            
        }catch(Exception $e){
            return response()->json([
                "message"=>"error",
                "status"=>200,
                "error"=>$e->getMessage(),
                "data"=>null
            ]);
        }
    }

    /**
     * Collect all expenditures done by a user
     */
    public function doneby(Request $request){

        if(isset($request['from']) && !empty($request['from']) && isset($request['to']) && !empty($request['to'])){
            $list=collect(Expenditures::where('user_id','=',$request->user_id)
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->get());
            $listdata=$list->map(function ($item,$key){
                return $this->show($item);
            });
            return $listdata;
        }
        else{
            $from=date('Y-m-d');
            $list=collect(Expenditures::where('user_id','=',$request->user_id)
            ->whereBetween('created_at',[$from.' 00:00:00',$from.' 23:59:59'])->get());
            $listdata=$list->map(function ($item,$key){
                return $this->show($item);
            });
            return $listdata;
        }
    }

    public function searchdoneby(Request $request){
        $searchTerm = $request->query('keyword', '');
        $enterpriseId = $request->query('enterprise_id', 0);  
        $actualuser=$this->getinfosuser($request->query('user_id'));
        if ($actualuser['user_type']=='super_admin') {
            
            $list =Expenditures::leftJoin('accounts', 'expenditures.account_id', '=', 'accounts.id')
                ->where('expenditures.enterprise_id', '=', $enterpriseId)
                ->where(function($query) use ($searchTerm) {
                    $query->where('expenditures.motif', 'LIKE', "%$searchTerm%")
                        ->orWhere('expenditures.amount', 'LIKE', "%$searchTerm%")
                        ->orWhere('expenditures.uuid', 'LIKE', "%$searchTerm%")
                        ->orWhere('expenditures.done_at', 'LIKE', "%$searchTerm%")
                        ->orWhere('expenditures.beneficiary', 'LIKE', "%$searchTerm%")
                        ->orWhere('expenditures.is_validate', 'LIKE', "%$searchTerm%")
                        ->orWhere('accounts.name', 'LIKE', "%$searchTerm%")
                        ->orWhere('accounts.description', 'LIKE', "%$searchTerm%")
                        ->orWhere('accounts.uuid', 'LIKE', "%$searchTerm%")
                        ->orWhere('expenditures.status', 'LIKE', "%$searchTerm%");
                })
                ->select('expenditures.*')
                ->paginate(10)
                ->appends($request->query());


            $list->getCollection()->transform(function ($item){
                return $this->show($item);
            });
            return $list;

        } else {
            
            $list =Expenditures::leftJoin('accounts', 'expenditures.account_id', '=', 'accounts.id')
            ->where('expenditures.user_id', '=', $actualuser['id'])
            ->where(function($query) use ($searchTerm) {
                $query->where('expenditures.motif', 'LIKE', "%$searchTerm%")
                    ->orWhere('expenditures.amount', 'LIKE', "%$searchTerm%")
                    ->orWhere('expenditures.uuid', 'LIKE', "%$searchTerm%")
                    ->orWhere('expenditures.done_at', 'LIKE', "%$searchTerm%")
                    ->orWhere('expenditures.beneficiary', 'LIKE', "%$searchTerm%")
                    ->orWhere('expenditures.is_validate', 'LIKE', "%$searchTerm%")
                    ->orWhere('accounts.name', 'LIKE', "%$searchTerm%")
                    ->orWhere('accounts.description', 'LIKE', "%$searchTerm%")
                    ->orWhere('accounts.uuid', 'LIKE', "%$searchTerm%")
                    ->orWhere('expenditures.status', 'LIKE', "%$searchTerm%");
            })
            ->select('expenditures.*')
            ->paginate(10)
            ->appends($request->query());


        $list->getCollection()->transform(function ($item){
            return $this->show($item);
        });
        return $list;
        }
    }

    public function byaccount(Request $request){

        if(isset($request['from']) && !empty($request['from']) && isset($request['to']) && !empty($request['to'])){
            $list=collect(Expenditures::where('account_id','=',$request->account_id)
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->get());
            $listdata=$list->map(function ($item,$key){
                return $this->show($item);
            });
            return $listdata;
        }
        else{
            $list=collect(Expenditures::where('account_id','=',$request->account_id)->get());
            $listdata=$list->map(function ($item,$key){
                return $this->show($item);
            });
            return $listdata;
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
     * @param  \App\Http\Requests\StoreExpendituresRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreExpendituresRequest $request)
    {
        $response= new stdClass;
        $message="unknown";
        if (!$request['uuid']) {
            $request['uuid']=$this->getUuId('EX','C');
        }
        
        if(!$request['money_id']){
            $defaultmoney=$this->defaultmoney($request['enterprise_id']);
            $request['money_id']=$defaultmoney['id'];
        } 
        
        if(!$request['done_at']){
            $request['done_at']=date('Y-m-d');
        }

        $ifexists=UsersExpendituresLimits::join('expenditures_limits as EL','users_expenditures_limits.limit_id','=','EL.id')->where('users_expenditures_limits.user_id','=',$request['user_id'])->get()->first();
        if($ifexists){
            if ($request['amount']<=$ifexists['maximum']) {
                $message="success";
                $response=$this->show(Expenditures::create($request->all()));
            }else{
                $message="unauthorized";
            }
        }else{
            $message="success";
            $response=$this->show(Expenditures::create($request->all()));
        }
        $response->message=$message;
        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Expenditures  $expenditures
     * @return \Illuminate\Http\Response
     */
    public function show(Expenditures $expenditures)
    {
        return Expenditures::leftjoin('moneys as M','expenditures.money_id','=','M.id')
        ->leftjoin('accounts as A','expenditures.account_id','=','A.id')
        ->leftjoin('users as U','expenditures.user_id','=','U.id')
        ->leftjoin('servants as S','expenditures.beneficiary','=','S.id')
        ->where('expenditures.id','=',$expenditures->id)
        ->get(['S.email as beneficiary_mail','S.name as beneficiary_name','S.phone as beneficiary_phone','M.money_name','M.abreviation','A.name as account_name','U.user_name','expenditures.*'])->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Expenditures  $expenditures
     * @return \Illuminate\Http\Response
     */
    public function edit(Expenditures $expenditures)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateExpendituresRequest  $request
     * @param  \App\Models\Expenditures  $expenditures
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateExpendituresRequest $request, Expenditures $expenditures)
    {
        return $this->show(Expenditures::find( $expenditures->update($request->all())));
    }

    /**
     * delete operation
     */
    public function delete($expenditures){
        $expenditures=Expenditures::find($expenditures);
        return $expenditures->delete();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Expenditures  $expenditures
     * @return \Illuminate\Http\Response
     */
    public function destroy(Expenditures $expenditures)
    {
        $expenditures=Expenditures::find($expenditures);
        return Expenditures::destroy($expenditures);
    }

    /**
     * update 
     */
    public function expendituresupdate(Request $request){
        $data=[];
        if ($request['criteria']) {
            if ($request['user'] && $this->getEse($request['user']['id'])) {
                if ($request['data'] && count($request['data'])>0) {
                    try {
                        $status="";
                        switch ($request['criteria']) {
                            case 'validate':
                                $status="validated";
                                break;
                            case 'pending':
                                $status="pending";
                                break;
                            case 'cancel':
                                $status="cancelled";
                                break;
                            
                            default:
                                # code...
                                break;
                        }

                        foreach ($request['data'] as $expenditure) {
                            $finded=Expenditures::find($expenditure['id']);
                            if ($finded){
                                $finded['status']=$status;
                                if ($status=="validated") {
                                    $finded['is_validated']=true;
                                }else{
                                    $finded['is_validated']=false;
                                } 
                                
                                $finded->save();
                                array_push($data,$finded);
                            }
                         }
                            return response()->json([
                            "status"=>200,
                            "message"=>"success",
                            "error"=>null,
                            "data"=>$data
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
                        "error"=>"no data sent",
                        "data"=>null
                    ]); 
                }
            }else{
                //unauthorized user
                return response()->json([
                    "status"=>400,
                    "message"=>"error",
                    "error"=>"unauthrized sent",
                    "data"=>null
                ]); 
            }
        }else{
            //no criteria sent
            return response()->json([
                "status"=>400,
                "message"=>"error",
                "error"=>"no criteria sent",
                "data"=>null
            ]); 
        }
    }
}
