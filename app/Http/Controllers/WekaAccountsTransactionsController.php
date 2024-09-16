<?php

namespace App\Http\Controllers;

use App\Models\wekaAccountsTransactions;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorewekaAccountsTransactionsRequest;
use App\Http\Requests\UpdatewekaAccountsTransactionsRequest;
use App\Models\wekamemberaccounts;
use Illuminate\Support\Facades\DB;

class WekaAccountsTransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($ese)
    {
        $list=[];
        try {
            $list1=collect(wekaAccountsTransactions::where('enterprise_id',$ese)->get());
            $list=$list1->transform(function($item){
                return $this->show($item);
            });

            return response()->json([
                "status"=>200,
                "message"=>"success",
                "error"=>null,
                "data"=>$list
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status"=>500,
                "message"=>"error occured",
                "error"=>$th,
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
        //if exist the account and not suspended
        if ($request['member_account_id']) {
            //looking for the account
            $memberaccount=wekamemberaccounts::find($request['member_account_id']);
            if ($memberaccount) {
                //if the account is enabled
                if($memberaccount->account_status=="enabled"){
                    //if withdraw test the sold before making request
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
                                    'sold_before'=>$memberaccount->sold,
                                    'sold_after'=> $memberaccountupdated->sold,
                                    'type'=>$request['type'],
                                    'motif'=>$request['motif'],
                                    'user_id'=>$request['user_id'],
                                    'member_account_id'=>$memberaccount->id,
                                    'enterprise_id'=>$memberaccount->enterprise_id,
                                    'done_at'=>$request['done_at'],
                                    'account_id'=>$request['account_id'],
                                ]);
                                DB::commit();
                                return response()->json([
                                    "status"=>200,
                                    "message"=>"success",
                                    "error"=>null,
                                    "data"=>$this->show($savewithdrawtransaction)
                                ]);
                            } catch (\Throwable $th) {
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
                    if ($request['type']=='entry') {
                            //begin transaction
                            DB::beginTransaction();
                            try {
                                $memberaccountupdated=$memberaccount;
                                $memberaccountupdated->sold=$memberaccount->sold+$request['amount'];
                                $memberaccountupdated->save();
                                $savewithdrawtransaction=wekaAccountsTransactions::create([
                                    'amount'=>$request['amount'],
                                    'sold_before'=>$memberaccount->sold,
                                    'sold_after'=> $memberaccountupdated->sold,
                                    'type'=>$request['type'],
                                    'motif'=>$request['motif'],
                                    'user_id'=>$request['user_id'],
                                    'member_account_id'=>$memberaccount->id,
                                    'enterprise_id'=>$memberaccount->enterprise_id,
                                    'done_at'=>$request['done_at'],
                                    'account_id'=>$request['account_id'],
                                ]);
                                DB::commit();
                                return response()->json([
                                    "status"=>200,
                                    "message"=>"success",
                                    "error"=>null,
                                    "data"=>$this->show($savewithdrawtransaction)
                                ]);
                            } catch (\Throwable $th) {
                                DB::rollBack();
                                //throw $th;
                                return response()->json([
                                    "status"=>500,
                                    "message"=>"error occured",
                                    "error"=>$th,
                                    "data"=>null
                                ]);
                            }
                      
                    }
                }else{
                    return response()->json([
                        "status"=>401,
                        "message"=>"error occured",
                        "error"=>"account disabled",
                        "data"=>null
                    ]);
                }
            }else{
                return response()->json([
                    "status"=>401,
                    "message"=>"error occured",
                    "error"=>"no account find",
                    "data"=>null
                ]); 
            }
        }else{
            return response()->json([
                "status"=>400,
                "message"=>"error occured",
                "error"=>"no account sent",
                "data"=>null
            ]);  
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
        ->leftjoin('accounts as A','weka_accounts_transactions.account_id','A.id')
        ->where('weka_accounts_transactions.id','=',$wekaAccountsTransactions->id)
        ->get(['weka_accounts_transactions.*','A.name as account_name','WA.description as memberaccount_name','M.abreviation','users.user_name'])->first();
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
