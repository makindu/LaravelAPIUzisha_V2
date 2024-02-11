<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Debts;
use App\Models\Fences;
use App\Models\Accounts;
use App\Models\Invoices;
use App\Models\Expenditures;
use App\Models\OtherEntries;
use Illuminate\Http\Request;
use App\Models\usersenterprise;
use App\Models\affectation_users;
use App\Models\Cautions;
use App\Models\DebtPayments;
use App\Models\DepositController;
use App\Models\DepositsUsers;
use App\Models\money_conversion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use stdClass;

class UsersController extends Controller
{
    public function index($enterprise_id)
    {
        $list=collect(usersenterprise::where('enterprise_id','=',$enterprise_id)->get());
        $listdata=$list->map(function ($item){
            return $this->show(user::find($item['user_id']));
        });
        return $listdata;
    }

    public function dashboard(Request $request,$userId){
        $user=$this->getinfosuser($userId);
        $ese=$this->getEse($user['id']);
        $defautmoney=$this->defaultmoney($ese['id']);
        $message='';
        $total_cash=0;
        $total_credits=0;
        $total_entries=0;
        $total_expenditures=0;
        $total_fences=0;
        $total_debts=0;
        $total_accounts=0;
        
        $cash=[];
        $credits=[];
        $entries=[];
        $expenditures=[];
        $fences=[];
        $accounts=[];
        $debts=[];

        if ($user) {
            if (empty($request['from']) && empty($request['to'])) {
                $request['from']=date('Y-m-d');
                $request['to']=date('Y-m-d');
            } 
            
            //getting data for the Super Admin
            if ($user['user_type']=='super_admin') {
                //cash
                $cash=Invoices::whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('type_facture','=','cash')->where('enterprise_id','=',$ese['id'])->get();
                foreach ($cash as $invoice) {
                    if ($defautmoney['id']==$invoice['money_id']) {
                        $total_cash=$total_cash+$invoice['total'];
                    } else {
                        $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$invoice['money_id'])->first();
                        if(!$rate){
                             $total_cash=($total_cash+$invoice['total'])*0;
                        }else{
                             $total_cash=($total_cash+$invoice['total'])* $rate['rate'];
                        } 
                    }
                }
                //credit
                $credits=Invoices::leftjoin('debts as D','invoices.id','=','D.invoice_id')->whereBetween('invoices.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('invoices.type_facture','=','credit')->where('invoices.enterprise_id','=',$ese['id'])->get(['invoices.*','D.sold']);
                foreach ($credits as $invoice) {
                    if ($defautmoney['id']==$invoice['money_id']) {
                        $total_credits=$total_credits+$invoice['sold'];
                    } else {
                        $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$invoice['money_id'])->first();
                        if(!$rate){
                            $total_credits=($total_credits+$invoice['sold'])*0;
                        }else{
                             $total_credits=($total_credits+$invoice['sold'])* $rate['rate'];
                        } 
                    }
                }
                //entries
                $entries=OtherEntries::join('users as U','other_entries.user_id','=','U.id')->leftjoin('accounts as AC','other_entries.account_id','=','AC.id')->whereBetween('other_entries.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('other_entries.enterprise_id','=',$ese['id'])->get(['other_entries.*','AC.name as account_name','U.user_name']);
                foreach ($entries as $entry) {
                    if ($defautmoney['id']==$entry['money_id']) {
                        $total_entries=$total_entries+$entry['amount'];
                    } else {
                        $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$entry['money_id'])->first();
                        if(!$rate){
                            $total_entries=($total_entries+$entry['amount'])*0;
                        }else{
                            $total_entries=($total_entries+$entry['amount'])* $rate['rate']; 
                        } 
                    }
                }
                //expenditures
                $expenditures=Expenditures::join('users as U','expenditures.user_id','=','U.id')->leftjoin('accounts as AC','expenditures.account_id','=','AC.id')->whereBetween('expenditures.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('expenditures.enterprise_id','=',$ese['id'])->get(['expenditures.*','AC.name as account_name','U.user_name']);
                foreach ($expenditures as $expenditure) {
                    if ($defautmoney['id']==$expenditure['money_id']) {
                        $total_expenditures=$total_expenditures+$expenditure['amount'];
                    } else {
                        $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$expenditure['money_id'])->first();
                        if(!$rate){
                            $total_expenditures=($total_expenditures+$expenditure['amount'])*0;
                        }else{
                            $total_expenditures=($total_expenditures+$expenditure['amount'])* $rate['rate'];
                        }
                        
                    }
                }
                //fences
                $fences=Fences::join('users as U','fences.user_id','=','U.id')->whereBetween('fences.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('enterprise_id','=',$ese['id'])->get(['fences.*','U.user_name','U.avatar']);
                foreach ($fences as $fence) {
                    if(isset($fence['money_id'])){
                        if ($defautmoney['id']==$fence['money_id']) {
                            $total_fences=$total_fences+$fence['sold'];
                        } else {
                            $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$fence['money_id'])->first();
                            if(!$rate){
                                $total_fences=($total_fences+$fence['sold'])*0;
                            }else{
                                $total_fences=($total_fences+$fence['sold'])* $rate['rate'];
                            } 
                        }
                    } 
                    else{
                        $total_fences=$total_fences+$fence['sold']; 
                    }
                }
                //debts
                $debts=Debts::join('invoices as I','debts.invoice_id','=','I.id')->leftjoin('customer_controllers as C','debts.customer_id','=','C.id')->whereBetween('debts.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('I.enterprise_id','=',$ese['id'])->get(['debts.*','C.customerName','I.money_id']);
                foreach ($debts as $debt) {
                    if ($defautmoney['id']==$debt['money_id']) {
                        $total_debts=$total_debts+$debt['sold'];
                    } else {
                        $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$debt['money_id'])->first();
                        if(!$rate){
                            $total_debts=($total_debts+$debt['sold'])*0;
                        }else{
                            $total_debts=($total_debts+$debt['sold'])* $rate['rate'];
                        } 
                    }
                }
                //debts payment
                $payments=DebtPayments::leftjoin('debts as D','debt_payments.debt_id','=','D.id')->join('invoices as I','D.invoice_id','=','I.id')->whereBetween('debt_payments.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('I.enterprise_id','=',$ese['id'])->get(['debt_payments.*','I.money_id']);
                foreach ($payments as $payment) {
                    if ($defautmoney['id']==$payment['money_id']) {
                        $total_entries=$total_entries+$payment['amount_payed'];
                    } else {
                        $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$payment['money_id'])->first();
                        if(!$rate){
                            $total_entries=($total_entries+$payment['amount_payed'])*0;
                        }else{
                            $total_entries=($total_entries+$payment['amount_payed'])* $rate['rate'];
                        } 
                    }
                }
                //cautions
                $cautions=Cautions::whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('enterprise_id','=',$ese['id'])->get();
                foreach ($cautions as $caution) {
                    if ($defautmoney['id']==$caution['money_id']) {
                        $total_entries=$total_entries+$caution['amount'];
                    } else {
                        $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$caution['money_id'])->first();
                        if(!$rate){
                            $total_entries=($total_entries+$caution['amount'])*0;
                        }else{
                            $total_entries=($total_entries+$caution['amount'])* $rate['rate'];
                        } 
                    }
                }
                //accounts
                $accounts_list=Accounts::where('enterprise_id','=',$ese['id'])->get();
                foreach ($accounts_list as $account) {
                         //entries
                        $total_account_entries=0;
                        $account_entries=OtherEntries::whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('account_id','=',$account['id'])->get();
                        foreach ($account_entries as $entry) {
                            if ($defautmoney['id']==$entry['money_id']) {
                                $total_account_entries=$total_account_entries+$entry['amount'];
                            } else {
                                $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$entry['money_id'])->first();
                                if(!$rate){
                                    $total_account_entries=($total_account_entries+$entry['amount'])*0;
                                }else{
                                    $total_account_entries=($total_account_entries+$entry['amount'])* $rate['rate']; 
                                } 
                            }
                        }

                        //expenditures
                        $total_account_expenditures=0;
                        $account_expenditures=Expenditures::whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('account_id','=',$account['id'])->get();
                        foreach ($account_expenditures as $expenditure) {
                            if ($defautmoney['id']==$expenditure['money_id']) {
                                $total_account_expenditures=$total_account_expenditures+$expenditure['amount'];
                            } else {
                                $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$expenditure['money_id'])->first();
                                if(!$rate){
                                    $total_account_expenditures=($total_account_expenditures+$expenditure['amount'])*0;
                                }else{
                                    $total_account_expenditures=($total_account_expenditures+$expenditure['amount'])* $rate['rate'];
                                }
                                
                            }
                        }
                    $data=['account'=>$account,'entries_amount'=>$total_account_entries,'expenditures_amount'=>$total_account_expenditures];
                    array_push($accounts,$data);
                }
            } else {
                //getting data for others types of users according to its owners operations

                 //cash
                 $cash=Invoices::where('edited_by_id','=',$userId)->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('type_facture','=','cash')->where('enterprise_id','=',$ese['id'])->get();
                 foreach ($cash as $invoice) {
                     if ($defautmoney['id']==$invoice['money_id']) {
                         $total_cash=$total_cash+$invoice['total'];
                     } else {
                         $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$invoice['money_id'])->first();
                         if(!$rate){
                              $total_cash=($total_cash+$invoice['total'])*0;
                         }else{
                              $total_cash=($total_cash+$invoice['total'])* $rate['rate'];
                         } 
                     }
                 }
                 //credit
                 $credits=Invoices::leftjoin('debts as D','invoices.id','=','D.invoice_id')->where('invoices.edited_by_id','=',$userId)->whereBetween('invoices.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('invoices.type_facture','=','credit')->where('invoices.enterprise_id','=',$ese['id'])->get(['invoices.*','D.sold']);
                 foreach ($credits as $invoice) {
                     if ($defautmoney['id']==$invoice['money_id']) {
                         $total_credits=$total_credits+$invoice['sold'];
                     } else {
                         $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$invoice['money_id'])->first();
                         if(!$rate){
                             $total_credits=($total_credits+$invoice['sold'])*0;
                         }else{
                              $total_credits=($total_credits+$invoice['sold'])* $rate['rate'];
                         } 
                     }
                 }
                 //entries
                 $entries=OtherEntries::where('other_entries.user_id','=',$userId)->join('users as U','other_entries.user_id','=','U.id')->leftjoin('accounts as AC','other_entries.account_id','=','AC.id')->whereBetween('other_entries.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('other_entries.enterprise_id','=',$ese['id'])->get(['other_entries.*','AC.name as account_name','U.user_name']);
                 foreach ($entries as $entry) {
                     if ($defautmoney['id']==$entry['money_id']) {
                         $total_entries=$total_entries+$entry['amount'];
                     } else {
                         $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$entry['money_id'])->first();
                         if(!$rate){
                             $total_entries=($total_entries+$entry['amount'])*0;
                         }else{
                             $total_entries=($total_entries+$entry['amount'])* $rate['rate']; 
                         } 
                     }
                 }
                 //expenditures
                 $expenditures=Expenditures::where('expenditures.user_id','=',$userId)->join('users as U','expenditures.user_id','=','U.id')->leftjoin('accounts as AC','expenditures.account_id','=','AC.id')->whereBetween('expenditures.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('expenditures.enterprise_id','=',$ese['id'])->get(['expenditures.*','AC.name as account_name','U.user_name']);
                 foreach ($expenditures as $expenditure) {
                     if ($defautmoney['id']==$expenditure['money_id']) {
                         $total_expenditures=$total_expenditures+$expenditure['amount'];
                     } else {
                         $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$expenditure['money_id'])->first();
                         if(!$rate){
                             $total_expenditures=($total_expenditures+$expenditure['amount'])*0;
                         }else{
                             $total_expenditures=($total_expenditures+$expenditure['amount'])* $rate['rate'];
                         }
                         
                     }
                 }
                 //fences
                 $fences=Fences::where('fences.user_id','=',$userId)->join('users as U','fences.user_id','=','U.id')->whereBetween('fences.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('enterprise_id','=',$ese['id'])->get(['fences.*','U.user_name','U.avatar']);
                 foreach ($fences as $fence) {
                    if(isset($fence['money_id'])){
                        if ($defautmoney['id']==$fence['money_id']) {
                            $total_fences=$total_fences+$fence['sold'];
                        } else {
                            $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$fence['money_id'])->first();
                            if(!$rate){
                                $total_fences=($total_fences+$fence['sold'])*0;
                            }else{
                                $total_fences=($total_fences+$fence['sold'])* $rate['rate'];
                            } 
                        }
                    } 
                    else{
                        $total_fences=$total_fences+$fence['sold']; 
                    }
                 }
                 //debts
                 $debts=Debts::where('debts.created_by_id','=',$userId)->join('invoices as I','debts.invoice_id','=','I.id')->leftjoin('customer_controllers as C','debts.customer_id','=','C.id')->whereBetween('debts.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('I.enterprise_id','=',$ese['id'])->get(['debts.*','C.customerName','I.money_id']);
                 foreach ($debts as $debt) {
                     if ($defautmoney['id']==$debt['money_id']) {
                         $total_debts=$total_debts+$debt['sold'];
                     } else {
                         $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$debt['money_id'])->first();
                         if(!$rate){
                             $total_debts=($total_debts+$debt['sold'])*0;
                         }else{
                             $total_debts=($total_debts+$debt['sold'])* $rate['rate'];
                         } 
                     }
                 }
                 //debts payment
                $payments=DebtPayments::leftjoin('debts as D','debt_payments.debt_id','=','D.id')->join('invoices as I','D.invoice_id','=','I.id')->where('debt_payments.done_by_id','=',$userId)->whereBetween('debt_payments.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('I.enterprise_id','=',$ese['id'])->get(['debt_payments.*','I.money_id']);
                foreach ($payments as $payment) {
                    if ($defautmoney['id']==$payment['money_id']) {
                        $total_entries=$total_entries+$payment['amount_payed'];
                    } else {
                        $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$payment['money_id'])->first();
                        if(!$rate){
                            $total_entries=($total_entries+$payment['amount_payed'])*0;
                        }else{
                            $total_entries=($total_entries+$payment['amount_payed'])* $rate['rate'];
                        } 
                    }
                }
                 //cautions
                 $cautions=Cautions::whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('enterprise_id','=',$ese['id'])->where('user_id','=',$userId)->get();
                 foreach ($cautions as $caution) {
                     if ($defautmoney['id']==$caution['money_id']) {
                         $total_entries=$total_entries+$caution['amount'];
                     } else {
                         $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$caution['money_id'])->first();
                         if(!$rate){
                             $total_entries=($total_entries+$caution['amount'])*0;
                         }else{
                             $total_entries=($total_entries+$caution['amount'])* $rate['rate'];
                         } 
                     }
                 }
                 //accounts
                 $accounts_list=Accounts::where('enterprise_id','=',$ese['id'])->get();
                 foreach ($accounts_list as $account) {
                          //entries
                         $total_account_entries=0;
                         $account_entries=OtherEntries::where('other_entries.user_id','=',$userId)->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('account_id','=',$account['id'])->get();
                         foreach ($account_entries as $entry) {
                             if ($defautmoney['id']==$entry['money_id']) {
                                 $total_account_entries=$total_account_entries+$entry['amount'];
                             } else {
                                 $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$entry['money_id'])->first();
                                 if(!$rate){
                                     $total_account_entries=($total_account_entries+$entry['amount'])*0;
                                 }else{
                                     $total_account_entries=($total_account_entries+$entry['amount'])* $rate['rate']; 
                                 } 
                             }
                         }
 
                         //expenditures
                         $total_account_expenditures=0;
                         $account_expenditures=Expenditures::where('expenditures.user_id','=',$userId)->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('account_id','=',$account['id'])->get();
                         foreach ($account_expenditures as $expenditure) {
                             if ($defautmoney['id']==$expenditure['money_id']) {
                                 $total_account_expenditures=$total_account_expenditures+$expenditure['amount'];
                             } else {
                                 $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$expenditure['money_id'])->first();
                                 if(!$rate){
                                     $total_account_expenditures=($total_account_expenditures+$expenditure['amount'])*0;
                                 }else{
                                     $total_account_expenditures=($total_account_expenditures+$expenditure['amount'])* $rate['rate'];
                                 }
                                 
                             }
                         }
                     $data=['account'=>$account,'entries_amount'=>$total_account_entries,'expenditures_amount'=>$total_account_expenditures];
                     array_push($accounts,$data);
                 }
                
            }
            
            $msg="fund";

        } else {
          $msg="not fund";
        }
        
        return ['total_accounts'=>$total_account_entries+$total_account_expenditures,'default_money'=>$defautmoney,'from'=>$request['from'],'to'=>$request['to'],'message'=>$msg,'total_cash'=>$total_cash,'total_credits'=>$total_credits,'total_entries'=>$total_entries,'total_expenditures'=>$total_expenditures,'total_fences'=>$total_fences,'total_debts'=>$total_debts,'cash'=>$cash,'credits'=>$credits,'expenditures'=>$expenditures,'entries'=>$entries,'fences'=>$fences,'debts'=>$debts,'accounts'=>$accounts];
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
     * make user as superAdmin
     */
    public function makeassuperadmin(Request $request){
        // return $request;
        if($request['user_type']==='super_admin'){
            $type="admin";
        }else{
            $type="super_admin";
        }
        DB::update('update users set user_type =? where id = ?',[$type,$request['id']]);
        return $this->show(User::find($request['id']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userS = user::create($request->all());
        if(isset($request->enterprise_id) && !empty($request->enterprise_id)){
             //affect user to the Ese
                usersenterprise::create([
                    'user_id'=>$userS->id,
                    'enterprise_id'=>$request->enterprise_id
                ]);
        }

        //affect user to the deposit
        if(isset($request['deposit_id']) && !empty($request['deposit_id'])){
            $deposit=DepositController::find($request['deposit_id']);
            if ($deposit) {
                DepositsUsers::create([
                    'deposit_id'=>$deposit['id'],
                    'user_id'=>$userS['id'],
                    'level'=>'simple'
                ]);
            }
        }

        $userSave=$this->show(User::find($userS['id']));

        if(isset($request->level) && isset($request->department_id)){
            // verification si il existe un utilisatair de type admin deja affecter
            if ($request->level == 'chief') {

                $ifIsChief = DB::table('affectation_users')
                ->where('department_id','=', $request->department_id)
                ->where('level', '=', 'chief')
                ->get();

                if (count($ifIsChief) == 0) {
                    $departemetAffect = affectation_users::create(
                        ['user_id' => $userS['id'],
                        'level' => $request->level,
                        'department_id' => $request->department_id,
                    ]);
                    return [$userSave, $affected = 'succes'];
                }else {
                    return [$userSave, $affected ='error'];
                }
            }else{
                $departemetAffect = affectation_users::create(
                    ['user_id' => $userS['id'],
                    'level' => $request->level,
                    'department_id' => $request->department_id,
                ]);
                return [$userSave=$this->show(User::find($userS['id'])), $affected = 'succes'];
            }
        }else{
            return $userSave;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\user  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $usersent=User::leftjoin('roles as R', 'users.permissions','=','R.id')
        ->leftjoin('usersenterprises as E', 'users.id','=','E.user_id')
        ->where('users.id','=',$user->id)
        ->get(['users.*','R.title as role_title', 'R.description as role_description','R.permissions as role_permissions','E.enterprise_id'])->first();
        return $usersent;
    }

    public function getone($id){

        return User::leftjoin('affectation_users as A', 'users.id','=','A.user_id')
        ->leftjoin('departments as D', 'A.department_id','=','D.id')
        ->where('users.id', '=',$id)
        ->get(['D.department_name as department_name', 'D.id as department_id', 'users.*', 'A.level'])[0];

    }

    public function getuseraccess($id){

        $ouput=['user'=>'','type'=>'','access'=>'','can_validate'=>false];

        $user=User::leftjoin('affectation_users as A', 'users.id','=','A.user_id')
        ->leftjoin('departments as D', 'A.department_id','=','D.id')
        ->leftjoin('decision_teams as DC','users.id','=','DC.user_id')
        ->where('users.id', '=',$id)
        ->get(['D.department_name as department_name', 'D.id as department_id', 'users.*', 'A.level','DC.access']);

        $ouput['user']=$user;

        if($user[0]['department_name']){

            if($user[0]['level']=='chief'){
                $ouput['can_validate']=true;
            }

            $ouput['type']=$user[0]['level'];

        }elseif($user[0]['access']){

            $ouput['access']=$user[0]['access'];
            $ouput['type']='decision';

            if($user[0]['access']=='rw'){
                $ouput['can_validate']=true;
            }
        }else{

        }

        return $ouput;

    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\user  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(user $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @param  \App\Models\user  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, user $user)
    {
        $user->update($request->all());
        return $this->show($user);
    }

    public function update2(Request $request, $id)
    {
        $user=User::find($id);
        $user->update($request->all());

       return $this->show($user);
    } 
    
    public function changerStatus(Request $request)
    {
        DB::update('update users set status = ? where id = ?',[$request['status'],$request['user_id']]);
        return $this->show(User::find($request['user_id']));
    } 
    
    public function updatePassword(Request $request)
    {
        DB::update('update users set user_password = ? where id = ?',[$request['user_password'],$request['user_id']]);
        return $this->show(User::find($request['user_id']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\user  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(user $user)
    {
        return user::destroy($user);
    }

    public function destroy2($id){
        $user=User::find($id);
        return $user->delete();
    }

    public function login(Request $request){
        $message='';
        $actualEse= new stdClass;
        $user=User::leftjoin('usersenterprises as UE', 'users.id','=','UE.user_id')->leftjoin('roles as R', 'users.permissions','=','R.id')
        ->where('users.user_name',$request->user_name)
        ->where('users.user_password','=',$request->user_password)
        ->where('users.status','=','enabled')
        ->get(['users.*','UE.enterprise_id', 'permissions'=> 'R.*','R.title as role_title', 'R.description as role_description','id'=> 'users.id'])[0];
        if($user){
            $message="success";
            $actualEse=$this->getEse($user['id']);
            if ($actualEse) {
                $user['enterprise_id']=$actualEse['id'];
            }
            $user=$this->show($user);
        }else{
            $message='access denied';
        }
        return ['message'=>$message,'user'=>$user,'enterprise'=>$actualEse];
    }
}
