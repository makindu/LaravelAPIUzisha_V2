<?php

namespace App\Http\Controllers;

use App\Models\wekaAccountsTransactions;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorewekaAccountsTransactionsRequest;
use App\Http\Requests\UpdatewekaAccountsTransactionsRequest;
use App\Models\wekamemberaccounts;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WekaAccountsTransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
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

                                $list1=collect(wekaAccountsTransactions::whereIn('member_id',$request['members'])
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

                            $list1=collect(wekaAccountsTransactions::where('enterprise_id',$request['enterprise_id'])
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
     * Offline data gotten
     */
    public function syncing(Request $request){
        $datatoreturn = [];
        try {
            foreach ($request['offlinetransactions'] as  $value) {
               $newsync = $this->syncingstore(new Request($value));
                array_push($datatoreturn,$newsync);
            }

            return response()->json([
                "status"=>200,
                "message"=>"success",
                "error"=>null,
                "data"=>$datatoreturn
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
     * @param  \App\Http\Requests\StorewekaAccountsTransactionsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorewekaAccountsTransactionsRequest $request)
    {
        $soldbefore=0;
        $soldafter=0;
        //if exist the account and not suspended
        if ($request['member_account_id']) {
            //looking for the account
            $memberaccount=wekamemberaccounts::find($request['member_account_id']);
            if ($memberaccount) {
                //if the account is enabled
                if($memberaccount->account_status=="enabled"){
                    //if withdraw test the sold before making request
                    $soldbefore=$memberaccount->sold;
                    if ($request['type']=='withdraw') {
                        //verify the sold vis the amount sent
                        if ($memberaccount->sold>=$request['amount']) {
                            //begin transaction
                            DB::beginTransaction();
                            try {
                                $memberaccountupdated=$memberaccount;
                                $memberaccountupdated->sold=$memberaccount->sold-$request['amount'];
                                $memberaccountupdated->save();
                                $savewithdrawtransaction=wekaAccountsTransactions::create([
                                    'amount'=>$request['amount'],
                                    'sold_before'=>$soldbefore,
                                    'sold_after'=> $memberaccountupdated->sold,
                                    'type'=>$request['type'],
                                    'motif'=>$request['motif'],
                                    'user_id'=>$request['user_id'],
                                    'member_account_id'=>$memberaccount->id,
                                    'member_id'=>$memberaccount->user_id,
                                    'enterprise_id'=>$memberaccount->enterprise_id,
                                    'done_at'=>$request['done_at']?$request['done_at']:date('Y-m-d'),
                                    'account_id'=>$request['account_id'],
                                    'operation_done_by'=>$request['operation_done_by'],
                                    'uuid'=>$this->getUuId('WEKA','OP'),
                                    'fees'=>$request['fees']
                                ]);
                                DB::commit();
                                return response()->json([
                                    "status"=>200,
                                    "message"=>"success",
                                    "error"=>null,
                                    "data"=>$this->show($savewithdrawtransaction)
                                ]);
                            } catch (Exception $th) {
                                DB::rollBack();
                                //throw $th;
                                return response()->json([
                                    "status"=>500,
                                    "message"=>"error occured",
                                    "error"=>$th,
                                    "data"=>null
                                ]);
                            }
                        }else {
                            return response()->json([
                                "status"=>401,
                                "message"=>"error occured",
                                "error"=>"sold not enough",
                                "data"=>null
                            ]);
                        }
                    }

                    //if is entry
                    if ($request['type']=='deposit') {
                            //begin transaction
                            DB::beginTransaction();
                            try {
                                $memberaccountupdated=$memberaccount;
                                $memberaccountupdated->sold=$memberaccount->sold+$request['amount'];
                                $memberaccountupdated->save();
                                $savewithdrawtransaction=wekaAccountsTransactions::create([
                                    'amount'=>$request['amount'],
                                    'sold_before'=>$soldbefore,
                                    'sold_after'=> $memberaccountupdated->sold,
                                    'type'=>$request['type'],
                                    'motif'=>$request['motif'],
                                    'user_id'=>$request['user_id'],
                                    'member_account_id'=>$memberaccount->id,
                                    'member_id'=>$memberaccount->user_id,
                                    'enterprise_id'=>$memberaccount->enterprise_id,
                                    'done_at'=>$request['done_at']?$request['done_at']:date('Y-m-d'),
                                    'account_id'=>$request['account_id'],
                                    'operation_done_by'=>$request['operation_done_by'],
                                    'uuid'=>$this->getUuId('WEKA','OP'),
                                    'fees'=>$request['fees']
                                ]);
                                DB::commit();
                                return response()->json([
                                    "status"=>200,
                                    "message"=>"success",
                                    "error"=>null,
                                    "data"=>$this->show($savewithdrawtransaction)
                                ]);
                            } catch (Exception $th) {
                                DB::rollBack();
                                //throw $th;
                                return response()->json([
                                    "status"=>500,
                                    "message"=>"error",
                                    "error"=>$th,
                                    "data"=>null
                                ]);
                            }
                      
                    }
                }else{
                    return response()->json([
                        "status"=>401,
                        "message"=>"error",
                        "error"=>"account disabled",
                        "data"=>null
                    ]);
                }
            }else{
                return response()->json([
                    "status"=>401,
                    "message"=>"error",
                    "error"=>"no account find",
                    "data"=>null
                ]); 
            }
        }else{
            return response()->json([
                "status"=>400,
                "message"=>"error",
                "error"=>"no account sent",
                "data"=>null
            ]);  
        }
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorewekaAccountsTransactionsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function syncingstore(Request $request)
    {
        $soldbefore=0;
        $soldafter=0;
        // dump($request);
        //if exist the account and not suspended
        if ($request['member_account_id']) {
            //looking for the account
            $memberaccount=wekamemberaccounts::find($request['member_account_id']);
            if ($memberaccount) {
                //if the account is enabled
                if($memberaccount->account_status=="enabled"){
                    //if withdraw test the sold before making request
                    $soldbefore=$memberaccount->sold;
                    if ($request['type']=='withdraw') {
                        //verify the sold vis the amount sent
                        if ($memberaccount->sold>=$request['amount']) {
                            //begin transaction
                            DB::beginTransaction();
                            try {
                                $memberaccountupdated=$memberaccount;
                                $memberaccountupdated->sold=$memberaccount->sold-$request['amount'];
                                $memberaccountupdated->save();
                                $savewithdrawtransaction=wekaAccountsTransactions::create([
                                    'amount'=>$request['amount'],
                                    'sold_before'=>$soldbefore,
                                    'sold_after'=> $memberaccountupdated->sold,
                                    'type'=>$request['type'],
                                    'motif'=>$request['motif'],
                                    'user_id'=>$request['user_id'],
                                    'member_account_id'=>$memberaccount->id,
                                    'member_id'=>$memberaccount->user_id,
                                    'enterprise_id'=>$memberaccount->enterprise_id,
                                    'done_at'=>$request['done_at']?$request['done_at']:date('Y-m-d'),
                                    'account_id'=>$request['account_id'],
                                    'operation_done_by'=>$request['operation_done_by'],
                                    'uuid'=>$request['uuid']?$request['uuid']:$this->getUuId('WEKA','OP'),
                                    'fees'=>$request['fees']
                                ]);
                                DB::commit();
                                $original=$this->show($savewithdrawtransaction);
                                $original['error']=null;
                                $original['message']="success";
                                return $original;
                            } catch (Exception $th) {
                                DB::rollBack();
                                //throw $th;
                                $request['error']=$th->getMessage();
                                $request['message']="error";
                                return $request->all();
                            }
                        }else {
                            $request['error']="sold not enough";
                            $request['message']="error";
                            return $request->all();
                        }
                    }

                    //if is entry
                    if ($request['type']=='deposit') {
                            //begin transaction
                            DB::beginTransaction();
                            try {
                                $memberaccountupdated=$memberaccount;
                                $memberaccountupdated->sold=$memberaccount->sold+$request['amount'];
                                $memberaccountupdated->save();
                                $savewithdrawtransaction=wekaAccountsTransactions::create([
                                    'amount'=>$request['amount'],
                                    'sold_before'=>$soldbefore,
                                    'sold_after'=> $memberaccountupdated->sold,
                                    'type'=>$request['type'],
                                    'motif'=>$request['motif'],
                                    'user_id'=>$request['user_id'],
                                    'member_account_id'=>$memberaccount->id,
                                    'member_id'=>$memberaccount->user_id,
                                    'enterprise_id'=>$memberaccount->enterprise_id,
                                    'done_at'=>$request['done_at']?$request['done_at']:date('Y-m-d'),
                                    'account_id'=>$request['account_id'],
                                    'operation_done_by'=>$request['operation_done_by'],
                                    'uuid'=>$request['uuid']?$request['uuid']:$this->getUuId('WEKA','OP'),
                                    'fees'=>$request['fees']
                                ]);
                                DB::commit();
                                $original=$this->show($savewithdrawtransaction);
                                $original['error']=null;
                                $original['message']="success";
                                return $original;
                            } catch (Exception $th) {
                                DB::rollBack();
                                $request['error']=$th->getMessage();
                                $request['message']="error";
                                return $request->all(); 
                            }
                    }
                }else{
                    $request['error']="account disabled";
                    $request['message']="error";
                    return $request->all(); 
                }
            }else{
                $request['error']="no account sent";
                $request['message']="error";
                return $request->all(); 
            }
        }else{
            $request['error']="no account find";
            $request['message']="error";
            return $request->all();  
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\wekaAccountsTransactions  $wekaAccountsTransactions
     * @return \Illuminate\Http\Response
     */
    public function show(wekaAccountsTransactions $wekaAccountsTransactions)
    {
        return wekaAccountsTransactions::join('users','weka_accounts_transactions.user_id','=','users.id')
        ->join('wekamemberaccounts as WA','weka_accounts_transactions.member_account_id','WA.id')
        ->join('moneys as M','WA.money_id','M.id')
        ->join('users as AU','WA.user_id','AU.id')
        ->leftjoin('accounts as A','weka_accounts_transactions.account_id','A.id')
        ->where('weka_accounts_transactions.id','=',$wekaAccountsTransactions->id)
        ->get(['AU.user_name as member_user_name','AU.full_name as member_fullname','AU.uuid as member_uuid','weka_accounts_transactions.*','A.name as account_name','WA.description as memberaccount_name','M.abreviation','users.user_name as done_by_name','users.full_name as done_by_fullname','users.uuid as done_by_uuid'])->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\wekaAccountsTransactions  $wekaAccountsTransactions
     * @return \Illuminate\Http\Response
     */
    public function edit(wekaAccountsTransactions $wekaAccountsTransactions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatewekaAccountsTransactionsRequest  $request
     * @param  \App\Models\wekaAccountsTransactions  $wekaAccountsTransactions
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatewekaAccountsTransactionsRequest $request, wekaAccountsTransactions $wekaAccountsTransactions)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\wekaAccountsTransactions  $wekaAccountsTransactions
     * @return \Illuminate\Http\Response
     */
    public function destroy(wekaAccountsTransactions $wekaAccountsTransactions)
    {
        //
    }
}
