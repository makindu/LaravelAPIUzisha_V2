<?php

namespace App\Http\Controllers;

use App\Models\wekamemberaccounts;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorewekamemberaccountsRequest;
use App\Http\Requests\UpdatewekamemberaccountsRequest;
use App\Models\moneys;
use App\Models\User;
use App\Models\wekaAccountsTransactions;
// use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Request;
use SebastianBergmann\Type\TypeName;
use stdClass;

class WekamemberaccountsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise)
    {
        //
    }

    /**
     * get all accounts paginated
     */
    public function allaccounts($user){
        $list=[];
        $actualuser=$this->getinfosuser($user);
        $ese=$this->getEse($user);
        if ($actualuser) {

            if ($actualuser['user_type']!=='super_admin') {
                $list= wekamemberaccounts::leftjoin('users as U', 'wekamemberaccounts.user_id','=','U.id')
                ->leftjoin('moneys as M', 'wekamemberaccounts.money_id','=','M.id')
                ->where('user_id','=',$user)
                ->get(['M.abreviation as money_abreviation', 'U.user_name', 'wekamemberaccounts.*']);
            }
            else{
                $list= wekamemberaccounts::leftjoin('users as U', 'wekamemberaccounts.user_id','=','U.id')
                ->leftjoin('moneys as M', 'wekamemberaccounts.money_id','=','M.id')
                ->where('wekamemberaccounts.enterprise_id',$ese->id)
                ->get(['M.abreviation as money_abreviation', 'U.user_name', 'wekamemberaccounts.*']);
            }

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
     * @param  \App\Http\Requests\StorewekamemberaccountsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorewekamemberaccountsRequest $request)
    {
        if (isset($request['sold']) &&  $request['sold']>0) {
            # code...
        }else{
            $request['sold']=0;
        }
               
        $newaccount=wekamemberaccounts::create($request->all());
        //make a new entry
        if($newaccount->sold>0){
            wekaAccountsTransactions::create(
                [
                    'amount'=>$newaccount->sold,
                    'done_at'=>date('Y-m-d'),
                    'user_id'=>$request->created_by,
                    'motif'=>'Balance d\'ouverture',
                    'type'=>'entry',
                    'enterprise_id'=>$request->enterprise_id,
                    'uuid'=>$this->getUuId('C','AT'),
                    'sold_before'=>0,
                    'sold_after'=>$newaccount->sold,
                ]
            );
        }
       
        return $this->show($newaccount);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\wekamemberaccounts  $wekamemberaccounts
     * @return \Illuminate\Http\Response
     */
    public function show(wekamemberaccounts $wekamemberaccounts)
    {
       return wekamemberaccounts::leftjoin('users as U', 'wekamemberaccounts.user_id','=','U.id')
        ->leftjoin('moneys as M', 'wekamemberaccounts.money_id','=','M.id')
        ->where('wekamemberaccounts.id',$wekamemberaccounts->id)->first(['M.abreviation as money_abreviation', 'U.user_name', 'wekamemberaccounts.*']);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\wekamemberaccounts  $wekamemberaccounts
     * @return \Illuminate\Http\Response
     */
    public function AccountUpdateSold(Request $request)
    {
        $request -> validate(
            [
                'user_id'=> 'required|integer|exists:users,id',
                'enterprise_id' => 'required|integer|exists:wekamemberaccounts,enterprise_id'

            ]);

            $data[] = $request['data'];
            $membersData = [];
            $seenCodes = [];
            $seenNames = []; 
            $warnigs = [];
            $problems = [];
            $success = [];
            $updated = false; 
            $alert = new stdClass();
            foreach ($data as $memberData) {

                if (isset($memberData['code'])){ 
                    $code_members = $memberData['code'];
                    $name_members  = $memberData['name'];
                    // Vérification des doublons par code et nom
                    if (in_array($code_members, $seenCodes) || in_array($name_members, $seenNames) ) {
                        $memberData['status'] = 'error';
                        $memberData['message'] = "le membre avec le code " . $code_members . " est repeter .";
                        // $membersData[] = $memberData;
                        array_push($problems,$memberData);
                        
                        continue; 
                    }
                    $seenCodes[] = $code_members; 
                    $seenNames[] = $name_members;
       
                    $member = User::where('id', $request['user_id'])->first();
                    
                    if ($member) {

                        $memberAccont = wekamemberaccounts::where('user_id',$member->id  )->first();
                        
                        foreach ($memberAccont as $foundaccount) 
                        {
                            $moneys = moneys::where('id' , $foundaccount->money_id )->first();
                            if ( $moneys->abreviation != $memberData['usd'] && $moneys->abreviation != $memberData['cdf'] ) 
                            {
                                $memberData['status'] = 'warning';
                                $memberData['actual_usd'] = $member->usd;
                                $memberData['message'] = "Le compte en ".$moneys->abreviation." du membre " . $member->full_name . " a un solde different avec celui dans votre fichier uploder";
                                array_push($warnigs,$memberData);
                            }else {
                                if ($updated) {
                                    $solde = $moneys->abreviation == $memberData['usd'] ? $memberData['usd'] : $memberData['usd'] ;
                                   
                                    $updating =  $foundaccount->update([
                                        'sold' => $solde , 
                                    ]);
                                    if ($updating) {
                                        $updatingHistory = wekaAccountsTransactions::create([
                                            'amount'=>$solde,
                                            'sold_before'=>0,
                                            'sold_after'=>$foundaccount->sold,
                                            'user_id'=>$member->id,
                                            'member_account_id'=>  $foundaccount->id,
                                            'enterprise_id'=>  $foundaccount->enterprise_id,
                                            'account_id'=>$foundaccount->account_number,
                                            'transaction_status'=>"pending",
                                            'sync_status'=>false,
                                        ]);
                                        $updatingHistory['statut'] = 'success';
                                        $updatingHistory['message'] = 'le solde a été mise à jour avec succée';
                                        array_push($success,$updatingHistory);
                                    }
                                    

                                }
                            }
                            
                        }
                        
                } else {
                    $memberData['status'] = 'error';
                    $memberData['message'] = "Le le membre " . $memberData['name'] . " n'a pas été trouvé";
                    }
                    // $membersData[] = $memberData;
                    // array_push($problems,$memberData);
    
                
            } else {
                if (empty($memberData['code'])) {
                    foreach ($memberData as $value) 
                    {
                        $value['message'] = "Le code pour le membre n'a pas été trouvé";
                        $alert->status = 'error';
                        # code...
                        // array_push($value,$alert);
                    }
                    // $memberData['message'] = "".gettype($memberData)."Le code pour le membre n'a pas été trouvé"  ;
                }else {
                $memberData['status'] = 'error';
                $memberData['message'] = "Le le membre  n'a pas été trouvé";
                }
                // $membersData[] = $memberData;
                array_push($problems,$memberData);
            }
       
           }
        
           return response()->json([
            "message" => 'ok',
            "succeded" => $success,
            "problems"=> $problems,
            "warnings"=> $warnigs,
            "status" => "success",
            "code" => 200
        ]);
        }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\wekamemberaccounts  $wekamemberaccounts
     * @return \Illuminate\Http\Response
     */
    public function edit(wekamemberaccounts $wekamemberaccounts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatewekamemberaccountsRequest  $request
     * @param  \App\Models\wekamemberaccounts  $wekamemberaccounts
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatewekamemberaccountsRequest $request, wekamemberaccounts $wekamemberaccounts)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\wekamemberaccounts  $wekamemberaccounts
     * @return \Illuminate\Http\Response
     */
    public function destroy(wekamemberaccounts $wekamemberaccounts)
    {
        //
    }
}
