<?php

namespace App\Http\Controllers;

use stdClass;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Debts;
use App\Models\funds;
use App\Models\Fences;
use App\Models\moneys;
use App\Models\Accounts;
use App\Models\Cautions;
use App\Models\Invoices;
use App\Mail\DefaultMail;
use App\Models\DebtPayments;
use App\Models\Expenditures;
use App\Models\OtherEntries;
use Illuminate\Http\Request;
use App\Models\DepositsUsers;
use App\Models\passwordreset;
use PHPMailer\PHPMailer\SMTP;
use App\Models\requestHistory;
use App\Models\usersenterprise;
use App\Models\money_conversion;
use App\Models\wekafirstentries;
use App\Models\affectation_users;
use App\Models\DepositController;
use App\Models\CustomerController;
use App\Models\wekamemberaccounts;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use App\Models\customerspointshistory;
use App\Models\wekaAccountsTransactions;

class UsersController extends Controller
{
    public function index($enterprise_id)
    {
        $list=collect(usersenterprise::join('users','usersenterprises.id','=','users.id')
        ->where('enterprise_id','=',$enterprise_id)->where('users.user_type','<>','member')->get(['usersenterprises.*']));
        $listdata=$list->map(function ($item){
            return $this->show(user::find($item['user_id']));
        });
        return $listdata;
    }

    /**
     * searching users with pagination
     */
    public function searchingusers(Request $request){
        $searchTerm = $request->query('keyword', '');
        $enterpriseId = $request->query('enterprise_id', 0);  
        $actualuser=$this->getinfosuser($request->query('user_id'));
        if ($actualuser) {
                $list =User::query()
                ->join('usersenterprises','users.id','=','usersenterprises.user_id')
                ->where('usersenterprises.enterprise_id', '=', $enterpriseId)
                ->where(function($query) use ($searchTerm) {
                    $query->where('user_name', 'LIKE', "%$searchTerm%")
                    ->orWhere('user_mail', 'LIKE', "%$searchTerm%")
                    ->orWhere('user_phone', 'LIKE', "%$searchTerm%")
                    ->orWhere('user_type', 'LIKE', "%$searchTerm%")
                    ->orWhere('status', 'LIKE', "%$searchTerm%")
                    ->orWhere('note', 'LIKE', "%$searchTerm%")
                    ->orWhere('full_name', 'LIKE', "%$searchTerm%")
                    ->orWhere('uuid', 'LIKE', "%$searchTerm%");
                })
                ->select('users.*')
                ->paginate(10)
                ->appends($request->query());

            $list->getCollection()->transform(function ($item){
                $data = $this->show($item);
                unset($data['role_permissions'],$data['user_password'],$data['remember_token']);
                return $data;
            });
            return $list;
        }else{
            return response()->json([
                "status"=>400,
                "data"=>null,
                "message"=>"incorrect data"
            ],400);
        }
    }

    public function members_validation(Request $request){
        if ($request['criteria']==="all" && $request['enterprise_id']){
            try{
                $enterpriseUsers=usersenterprise::join('users','usersenterprises.user_id','users.id')
                ->where('usersenterprises.enterprise_id','=',$request['enterprise_id'])
                ->where('users.status','disabled')
                ->select('usersenterprises.user_id')
                ->get(['usersenterprises.*']);
                $users=collect(user::whereIn('id',$enterpriseUsers->pluck('user_id'))->get());

                $updatedusers=$users->map(function ($user){
                    $user['status']="enabled";
                    $user->save();

                    $accountslist=collect(wekamemberaccounts::where('user_id',$user->id)
                    ->where('account_status','disabled')
                    ->get());
                    if(($accountslist->count())>0){
                        $accountslist->map(function ($account){
                            $account['account_status']="enabled";
                            $account->save();
                        }) ;
                    }
                    return $user;
                });

                return response()->json([
                    "status"=>200,
                    "message"=>"success",
                    "error"=>null,
                    "data"=>$updatedusers->count()
                ]);
            }catch(Exception $e){
                return response()->json([
                    "status"=>500,
                    "message"=>"error",
                    "error"=>$e->getMessage(),
                    "data"=>null
                ]);
            }
        }
    }

    public function ifexistsemailadress(Request $request){
        $user= new stdClass;
        if ($request['criteria']==="checking") {
          $user=User::where('user_mail','=',$request['value'])->first();

          if($user){
            // $request['token']=$this->getuuid('C','PS');
            // $request['email']=$user['user_mail'];
            $tokesent=passwordreset::create(
                [
                    'token'=>$this->getuuid('C','PS'),
                    'email'=>$user['user_mail']
                ]    
            );
            // $mail= new PHPMailer(true);
            //send mail
            // try {
            //     $request['token']=$this->getuuid('C','PS');
            //     $request['email']=$user['user_mail'];
            //     $tokesent=passwordreset::create($request);
            //     if ( $tokesent) {
            //         return response()->json([
            //             'data'=>$user,
            //             'status'=>200,
            //             'message'=>"find and mail sent"
            //         ]);
            //     }else{
            //         return response()->json([
            //             'data'=>$user,
            //             'status'=>204,
            //             'message'=>"find but mail not sent"
            //         ]);
            //     }
               

            // } catch (Exception $e) {
            //      return response()->json([
            //     'data'=>$user,
            //     'status'=>500,
            //     'message'=>"error sending mail".$e->getMessage()
            // ]);
            // }
            return response()->json([
                'data'=>$user,
                'status'=>200,
                'message'=>"find"
            ]);
           
          }else{
            return response()->json([
                'data'=>$user,
                'status'=>204,
                'message'=>"not find"
            ]);
          }
        }else{
            return response()->json([
                'data'=>$user,
                'status'=>204,
                'message'=>"no criteria"
            ]); 
        }

    }

    public function superadminfidelityreport(Request $request, $userId){
        $user=$this->getinfosuser($userId);
        $ese=$this->getEse($user['id']);
        $defautmoney=$this->defaultmoney($ese['id']);
          
        $totalEntriesPoints=0;
        $totalSellBypoints=0;
        $totalEntriesBonus=0;
        $totalSellByBonus=0;
        $totalEntriesCautions=0;
        $totalSellByCautions=0;

        if($user && $ese){
            //Points bloc
            $points=customerspointshistory::join('customer_controllers as C','customerspointshistories.customer_id','=','C.id')->select(DB::raw('sum(value) as totalPoints'))->whereBetween('customerspointshistories.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('customerspointshistories.type','=','point')->where('C.enterprise_id','=',$ese['id'])->get('totalPoints')->first();
            $totalEntriesPoints=$points['totalPoints'];
            
            $sellbypoints=Invoices::select(DB::raw('sum(netToPay) as totalSellByPoints'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('type_facture','=','point')->where('enterprise_id','=',$ese['id'])->get('totalSellByPoints')->first();
            $totalSellBypoints=$sellbypoints['totalSellByPoints'];

            //Bonus bloc
            $bonus=customerspointshistory::join('customer_controllers as C','customerspointshistories.customer_id','=','C.id')->select(DB::raw('sum(value) as totalBonus'))->whereBetween('customerspointshistories.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('customerspointshistories.type','=','bonus')->where('C.enterprise_id','=',$ese['id'])->get('totalBonus')->first();
            $totalEntriesBonus=$bonus['totalBonus'];
            
            $sellbybonus=Invoices::select(DB::raw('sum(netToPay) as totalSellByBonus'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('type_facture','=','bonus')->where('enterprise_id','=',$ese['id'])->get('totalSellByBonus')->first();
            $totalSellByBonus=$sellbybonus['totalSellByBonus'];

            //caution bloc
            $caution=Cautions::select(DB::raw('sum(amount) as totalCaution'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('enterprise_id','=',$ese['id'])->get('totalCaution')->first();
            $totalEntriesCautions=$caution['totalCaution'];
            
            $sellbycaution=Invoices::select(DB::raw('sum(netToPay) as totalSellByCautions'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('type_facture','=','caution')->where('enterprise_id','=',$ese['id'])->get('totalSellByCautions')->first();
            $totalSellByCautions=$sellbycaution['totalSellByCautions'];
        }
          

        return ['totalEntriesCautions'=>$totalEntriesCautions,'totalSellByCautions'=>$totalSellByCautions,'totalSellByBonus'=>$totalSellByBonus,'totalEntriesBonus'=>$totalEntriesBonus,'totalSellBypoints'=>$totalSellBypoints,'totalEntriesPoints'=>$totalEntriesPoints,'default_money'=>$defautmoney];
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
        
        $totalEntriesPoints=0;
        $totalSellBypoints=0;
        $totalEntriesBonus=0;
        $totalSellByBonus=0;
        $totalEntriesCautions=0;
        $totalSellByCautions=0;
        $cash=[];
        $credits=[];
        $entries=[];
        $expenditures=[];
        $fences=[];
        $accounts=[];
        $debts=[];

        $nbrmembersaccountstovalidate=0;

        if ($user) {
            if (empty($request['from']) && empty($request['to'])) {
                $request['from']=date('Y-m-d');
                $request['to']=date('Y-m-d');
            } 
            
            //getting data for the Super Admin
            if ($user['user_type']=='super_admin') {
                //members activations
                $listmemberstoactivate=usersenterprise::join('users','usersenterprises.user_id','users.id')
                ->where('usersenterprises.enterprise_id','=',$ese['id'])
                ->where('users.user_type','=','member')
                ->where('users.status','disabled')->get();
                $nbrmembersaccountstovalidate=$listmemberstoactivate->count();
                //fidelity report

                //Points bloc
                $points=customerspointshistory::join('customer_controllers as C','customerspointshistories.customer_id','=','C.id')->select(DB::raw('sum(value) as totalPoints'))->whereBetween('customerspointshistories.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('customerspointshistories.type','=','point')->where('C.enterprise_id','=',$ese['id'])->get('totalPoints')->first();
                $totalEntriesPoints=$points['totalPoints'];
                
                $sellbypoints=Invoices::select(DB::raw('sum(netToPay) as totalSellByPoints'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('type_facture','=','point')->where('enterprise_id','=',$ese['id'])->get('totalSellByPoints')->first();
                $totalSellBypoints=$sellbypoints['totalSellByPoints'];

                //Bonus bloc
                $bonus=customerspointshistory::join('customer_controllers as C','customerspointshistories.customer_id','=','C.id')->select(DB::raw('sum(value) as totalBonus'))->whereBetween('customerspointshistories.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('customerspointshistories.type','=','bonus')->where('C.enterprise_id','=',$ese['id'])->get('totalBonus')->first();
                $totalEntriesBonus=$bonus['totalBonus'];
                
                $sellbybonus=Invoices::select(DB::raw('sum(netToPay) as totalSellByBonus'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('type_facture','=','bonus')->where('enterprise_id','=',$ese['id'])->get('totalSellByBonus')->first();
                $totalSellByBonus=$sellbybonus['totalSellByBonus'];

                //caution bloc
                $caution=Cautions::select(DB::raw('sum(amount) as totalCaution'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('enterprise_id','=',$ese['id'])->get('totalCaution')->first();
                $totalEntriesCautions=$caution['totalCaution'];
                
                $sellbycaution=Invoices::select(DB::raw('sum(netToPay) as totalSellByCautions'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('type_facture','=','caution')->where('enterprise_id','=',$ese['id'])->get('totalSellByCautions')->first();
                $totalSellByCautions=$sellbycaution['totalSellByCautions'];

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
        
        return ['nbrmembersaccountstovalidate'=>$nbrmembersaccountstovalidate,'totalEntriesCautions'=>$totalEntriesCautions,'totalSellByCautions'=>$totalSellByCautions,'totalSellByBonus'=>$totalSellByBonus,'totalEntriesBonus'=>$totalEntriesBonus,'totalSellBypoints'=>$totalSellBypoints,'totalEntriesPoints'=>$totalEntriesPoints,'total_accounts'=>$total_account_entries+$total_account_expenditures,'default_money'=>$defautmoney,'from'=>$request['from'],'to'=>$request['to'],'message'=>$msg,'total_cash'=>$total_cash,'total_credits'=>$total_credits,'total_entries'=>$total_entries,'total_expenditures'=>$total_expenditures,'total_fences'=>$total_fences,'total_debts'=>$total_debts,'cash'=>$cash,'credits'=>$credits,'expenditures'=>$expenditures,'entries'=>$entries,'fences'=>$fences,'debts'=>$debts,'accounts'=>$accounts];
    } 
    
    private function wekagetexpenditures(Request $request){

        $moneys=collect(moneys::where("enterprise_id",$request['enterprise_id'])->get());
        $moneys->transform(function ($money) use($request){
            $histories=Expenditures::whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->where('money_id','=',$money['id'])
            ->get(); 

            $money['totalexpenditures']=$histories->sum('amount');
            return $money;
        });

        return $moneys;
    }   
    
    private function wekagetnumbermembers(Request $request){

        if($request['criteria']=="all"){
            $members=CustomerController::where('enterprise_id','=',$request['enterprise_id'])
            ->where('member_id','>',0)->get();
        }else{
            $members=CustomerController::where('enterprise_id','=',$request['enterprise_id'])
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->where('member_id','>',0)->get();
        }
        
        return $members->count();
    }  
    
    private function wekagetfirstentries(Request $request){

        $moneys=collect(moneys::where("enterprise_id",$request['enterprise_id'])->get());
        $moneys->transform(function ($money) use($request){
            $firstentries=wekafirstentries::where('enterprise_id','=',$request['enterprise_id'])
            ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->where('money_id','=',$money['id'])
            ->get();
            $money['totalfirstentries']=$firstentries->sum('amount');
            return $money;
        });

        return $moneys;

    }  
    
    private function wekagetsells(Request $request){

        $moneys=collect(moneys::where("enterprise_id",$request['enterprise_id'])->get());
        $moneys->transform(function ($money) use($request){
            $invoices=invoices::where('enterprise_id','=',$request['enterprise_id'])
            ->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->where('money_id','=',$money['id'])
            ->where('type_facture','<>','proforma')
            ->get();
            $money['totalsells']=$invoices->sum('netToPay');
            return $money;
        });

        return $moneys;

    }   
    
    private function wekagetaccountstransactions(Request $request){

        $moneys=collect(moneys::where("enterprise_id",$request['enterprise_id'])->get());
        $moneys->transform(function ($money) use($request){
            $transactions=wekaAccountsTransactions::join('wekamemberaccounts as WA','weka_accounts_transactions.member_account_id','WA.id')
            ->join('moneys as M','WA.money_id','M.id')
            ->whereBetween('weka_accounts_transactions.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->where('weka_accounts_transactions.enterprise_id','=',$request['enterprise_id'])
            ->where('weka_accounts_transactions.type','=',$request['criteria'])
            ->where('WA.money_id','=',$money['id'])
            ->get();

            $money['totaltransactions']=$transactions->sum('amount');

            return $money;
        });

        return $moneys;
    }

    public function wekafinancetotaltransactions(Request $request){
    
        $moneys=collect(moneys::where('enterprise_id',$request['enterprise_id'])->get());
        $moneys->transform(function ($money) use($request){
        
            $money['totalsoldtransactions']=0;
            $money['totalsoldtransactions']= $money['totalsoldtransactions']+($request['depositmembers']->where('id','=', $money['id'])->sum('totaltransactions'));
            $money['totalsoldtransactions']= $money['totalsoldtransactions']-($request['withdrawmembers']->where('id','=', $money['id'])->sum('totaltransactions'));

          return $money;
        });

        return $moneys;
    }
    
    public function wekafinancetotalrevenu(Request $request){
    
        $moneys=collect(moneys::where('enterprise_id',$request['enterprise_id'])->get());
        $moneys->transform(function ($money) use($request){
        
            $money['totalrevenu']=0;
            $money['totalrevenu']= $money['totalrevenu']+($request['firstentries']->where('id','=', $money['id'])->sum('totalfirstentries'));
            $money['totalrevenu']= $money['totalrevenu']+($request['sells']->where('id','=', $money['id'])->sum('totalsells'));

          return $money;
        });

        return $moneys;
    } 
    
    public function wekafinancebenefits(Request $request){
    
        $moneys=collect(moneys::where('enterprise_id',$request['enterprise_id'])->get());
        $moneys->transform(function ($money) use($request){
        
            $money['totalbenefits']=0;
            $money['totalbenefits']= $money['totalbenefits']+($request['totalrevenu']->where('id','=', $money['id'])->sum('totalrevenu'));
            $money['totalbenefits']= $money['totalbenefits']-($request['expenditures']->where('id','=', $money['id'])->sum('totalexpenditures'));

          return $money;
        });

        return $moneys;
    }  
    
    public function wekafinancesoldaccounts(Request $request){
    
        $moneys=collect(moneys::where('enterprise_id',$request['enterprise_id'])->get());
        $moneys->transform(function ($money) use($request){
            $money['sold']=0;
            $money['sold']=(wekamemberaccounts::where('enterprise_id','=', $request['enterprise_id'])->where('money_id',$money['id']))->sum('sold');
          return $money;
        });

        return $moneys;
    }

    public function wekafinancedashboard(Request $request,$userId){

        $user=$this->getinfosuser($userId);
        if($user && $user['user_type']=="super_admin"){
            $ese=$this->getEse($user['id']);
            if($ese && $ese['status']=="enabled"){
                if (empty($request['from']) && empty($request['to'])) {
                    $request['from']=date('Y-m-d');
                    $request['to']=date('Y-m-d');
                }
                try {
                   
                    $expenditures=$this->wekagetexpenditures(new Request(['from'=>$request['from'],'to'=>$request['to'],'enterprise_id'=>$ese['id']]));
                    $totalmembers=$this->wekagetnumbermembers(new Request(['criteria'=>'all','enterprise_id'=>$ese['id']]));
                    $nbmembers=$this->wekagetnumbermembers(new Request(['from'=>$request['from'],'to'=>$request['to'],'enterprise_id'=>$ese['id']]));
                    $firstentries=$this->wekagetfirstentries(new Request(['from'=>$request['from'],'to'=>$request['to'],'enterprise_id'=>$ese['id']]));
                    $sells=$this->wekagetsells(new Request(['from'=>$request['from'],'to'=>$request['to'],'enterprise_id'=>$ese['id']]));
                    $depositmembers=$this->wekagetaccountstransactions(new Request(['criteria'=>'deposit','from'=>$request['from'],'to'=>$request['to'],'enterprise_id'=>$ese['id']]));
                    $withdrawmembers=$this->wekagetaccountstransactions(new Request(['criteria'=>'withdraw','from'=>$request['from'],'to'=>$request['to'],'enterprise_id'=>$ese['id']]));
                    $soldtransactions=$this->wekafinancetotaltransactions(new Request(['depositmembers'=>$depositmembers,'withdrawmembers'=>$withdrawmembers,'enterprise_id'=>$ese['id']]));
                    $totalrevenu=$this->wekafinancetotalrevenu(new Request(['firstentries'=>$firstentries,'sells'=>$sells,'enterprise_id'=>$ese['id']]));
                    $befenefits=$this->wekafinancebenefits(new Request(['totalrevenu'=>$totalrevenu,'expenditures'=>$expenditures,'enterprise_id'=>$ese['id']]));

                    $intervals=[];
                    $periodicdashboard=[];
                    $fromdate=Carbon::parse($request['from']);
                    $todate=Carbon::parse($request['to']);
            
                    while($fromdate<=$todate){
                        array_push($intervals,$fromdate->toDateString());
                        $fromdate->addDay();
                    }

                    foreach ($intervals as $dateoperation) {
                        $request['from']=$dateoperation;
                        $request['to']=$dateoperation;

                        $periodicfirstentries=$this->wekagetfirstentries(new Request(['from'=>$request['from'],'to'=>$request['to'],'enterprise_id'=>$ese['id']]));
                        $periodicexpenditures=$this->wekagetexpenditures(new Request(['from'=>$request['from'],'to'=>$request['to'],'enterprise_id'=>$ese['id']]));
                        $periodicsells=$this->wekagetsells(new Request(['from'=>$request['from'],'to'=>$request['to'],'enterprise_id'=>$ese['id']]));
                        $periodictotalrevenu=$this->wekafinancetotalrevenu(new Request(['firstentries'=>$periodicfirstentries,'sells'=>$periodicsells,'enterprise_id'=>$ese['id']]));
                        $periodicbefenefits=$this->wekafinancebenefits(new Request(['totalrevenu'=>$periodictotalrevenu,'expenditures'=>$periodicexpenditures,'enterprise_id'=>$ese['id']]));
                        $soldaccountsnet=$this->wekafinancesoldaccounts(new Request(['enterprise_id'=>$ese['id']]));
                        $data=[
                            'date'=>$dateoperation,
                            'periodictotalrevenu'=>$periodictotalrevenu,
                            'periodicbefenefits'=>$periodicbefenefits,
                            'periodicexpenditures'=>$periodicexpenditures
                        ];

                        array_push($periodicdashboard,$data);
                    }
                    
                    return response()->json([
                        "status"=>200,
                        "message"=>"success",
                        "error"=>null,
                        "data"=>[
                            'totalrevenu'=>$totalrevenu,
                            'expenditures'=>$expenditures,
                            'benefits'=>$befenefits,
                            'nbmembers'=>$nbmembers,
                            'totalmembers'=>$totalmembers,
                            'firstentries'=>$firstentries,
                            'sells'=>$sells,
                            'depositmembers'=>$depositmembers,
                            'withdrawmembers'=>$withdrawmembers,
                            'soldtransactions'=>$soldtransactions,
                            'soldaccountsnet'=>$soldaccountsnet,
                            'periodicdashboard'=>$periodicdashboard
                        ]
                     ]);
                } catch (Exception $e) {
                    return response()->json([
                        'data'=>$user,
                        'status'=>500,
                        'error'=>$e->getMessage(),
                        'message'=>"error"
                    ]);
                }
            }else{
                return response()->json([
                    "status"=>400,
                    "message"=>"error",
                    "error"=>"enterprise not find or disabled",
                    "data"=>null
                 ]);
            }
        }else{
            return response()->json([
               "status"=>400,
                "message"=>"error",
                "error"=>"user unauthorized",
                "data"=>null
            ]);
        }
    }

    /**
     * dashboard based on date_operation and done_at
     */
    public function dashboardBasedDateOperation(Request $request,$userId){
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
        
        $totalEntriesPoints=0;
        $totalSellBypoints=0;
        $totalEntriesBonus=0;
        $totalSellByBonus=0;
        $totalEntriesCautions=0;
        $totalSellByCautions=0;
        $cash=[];
        $credits=[];
        $entries=[];
        $expenditures=[];
        $fences=[];
        $accounts=[];
        $debts=[];

        $nbrmembersaccountstovalidate=0;
        $nbrfirstentries=0;

        if ($user) {
            if (empty($request['from']) && empty($request['to'])) {
                $request['from']=date('Y-m-d');
                $request['to']=date('Y-m-d');
            } 
            
            //getting data for the Super Admin
            if ($user['user_type']=='super_admin') {
                  //members activations
                  $listmemberstoactivate=usersenterprise::join('users','usersenterprises.user_id','users.id')
                  ->where('usersenterprises.enterprise_id','=',$ese['id'])
                  ->where('users.user_type','=','member')
                  ->where('users.status','disabled')->get();
                  $nbrmembersaccountstovalidate=$listmemberstoactivate->count();

                //first entries
                $listfirstentries=wekafirstentries::where('enterprise_id',$ese['id'])
                                ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                                ->get();
                $nbrfirstentries=$listfirstentries->count();
                //fidelity report

                //Points bloc
                $points=customerspointshistory::join('customer_controllers as C','customerspointshistories.customer_id','=','C.id')->select(DB::raw('sum(value) as totalPoints'))->whereBetween('customerspointshistories.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('customerspointshistories.type','=','point')->where('C.enterprise_id','=',$ese['id'])->get('totalPoints')->first();
                $totalEntriesPoints=$points['totalPoints'];
                
                $sellbypoints=Invoices::select(DB::raw('sum(netToPay) as totalSellByPoints'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('type_facture','=','point')->where('enterprise_id','=',$ese['id'])->get('totalSellByPoints')->first();
                $totalSellBypoints=$sellbypoints['totalSellByPoints'];

                //Bonus bloc
                $bonus=customerspointshistory::join('customer_controllers as C','customerspointshistories.customer_id','=','C.id')->select(DB::raw('sum(value) as totalBonus'))->whereBetween('customerspointshistories.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('customerspointshistories.type','=','bonus')->where('C.enterprise_id','=',$ese['id'])->get('totalBonus')->first();
                $totalEntriesBonus=$bonus['totalBonus'];
                
                $sellbybonus=Invoices::select(DB::raw('sum(netToPay) as totalSellByBonus'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('type_facture','=','bonus')->where('enterprise_id','=',$ese['id'])->get('totalSellByBonus')->first();
                $totalSellByBonus=$sellbybonus['totalSellByBonus'];

                //caution bloc
                $caution=Cautions::select(DB::raw('sum(amount) as totalCaution'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('enterprise_id','=',$ese['id'])->get('totalCaution')->first();
                $totalEntriesCautions=$caution['totalCaution'];
                
                $sellbycaution=Invoices::select(DB::raw('sum(netToPay) as totalSellByCautions'))->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('type_facture','=','caution')->where('enterprise_id','=',$ese['id'])->get('totalSellByCautions')->first();
                $totalSellByCautions=$sellbycaution['totalSellByCautions'];

                //cash
                $cash=Invoices::whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('type_facture','=','cash')->where('enterprise_id','=',$ese['id'])->get();
                $total_cash=$cash->sum('total');
                // foreach ($cash as $invoice) {
                //     if ($defautmoney['id']==$invoice['money_id']) {
                //         $total_cash=$total_cash+$invoice['total'];
                //     } else {
                //         $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$invoice['money_id'])->first();
                //         if(!$rate){
                //              $total_cash=($total_cash+$invoice['total'])*0;
                //         }else{
                //              $total_cash=($total_cash+$invoice['total'])* $rate['rate'];
                //         } 
                //     }
                // }
                //credit
                $credits=Invoices::leftjoin('debts as D','invoices.id','=','D.invoice_id')->whereBetween('invoices.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('invoices.type_facture','=','credit')->where('invoices.enterprise_id','=',$ese['id'])->get(['invoices.*','D.sold']);
                $total_credits= $credits->sum('sold');
                // foreach ($credits as $invoice) {
                //     if ($defautmoney['id']==$invoice['money_id']) {
                //         $total_credits=$total_credits+$invoice['sold'];
                //     } else {
                //         $rate=money_conversion::where('money_id1','=',$defautmoney['id'])->where('money_id2','=',$invoice['money_id'])->first();
                //         if(!$rate){
                //             $total_credits=($total_credits+$invoice['sold'])*0;
                //         }else{
                //              $total_credits=($total_credits+$invoice['sold'])* $rate['rate'];
                //         } 
                //     }
                // }
                //entries
                $entries=OtherEntries::join('users as U','other_entries.user_id','=','U.id')->leftjoin('accounts as AC','other_entries.account_id','=','AC.id')->whereBetween('other_entries.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('other_entries.enterprise_id','=',$ese['id'])->get(['other_entries.*','AC.name as account_name','U.user_name']);
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
                $expenditures=Expenditures::join('users as U','expenditures.user_id','=','U.id')->leftjoin('accounts as AC','expenditures.account_id','=','AC.id')->whereBetween('expenditures.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('expenditures.enterprise_id','=',$ese['id'])->get(['expenditures.*','AC.name as account_name','U.user_name']);
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
                $fences=Fences::join('users as U','fences.user_id','=','U.id')->whereBetween('fences.date_concerned',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('enterprise_id','=',$ese['id'])->get(['fences.*','U.user_name','U.avatar']);
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
                $debts=Debts::join('invoices as I','debts.invoice_id','=','I.id')->leftjoin('customer_controllers as C','debts.customer_id','=','C.id')->whereBetween('debts.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('I.enterprise_id','=',$ese['id'])->get(['debts.*','C.customerName','I.money_id']);
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
                $payments=DebtPayments::leftjoin('debts as D','debt_payments.debt_id','=','D.id')->join('invoices as I','D.invoice_id','=','I.id')->whereBetween('debt_payments.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('I.enterprise_id','=',$ese['id'])->get(['debt_payments.*','I.money_id']);
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
                $cautions=Cautions::whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('enterprise_id','=',$ese['id'])->get();
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
                        $account_entries=OtherEntries::whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('account_id','=',$account['id'])->get();
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
                        $account_expenditures=Expenditures::whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('account_id','=',$account['id'])->get();
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
                 $cash=Invoices::where('edited_by_id','=',$userId)->whereBetween('date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('type_facture','=','cash')->where('enterprise_id','=',$ese['id'])->get();
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
                 $credits=Invoices::leftjoin('debts as D','invoices.id','=','D.invoice_id')->where('invoices.edited_by_id','=',$userId)->whereBetween('invoices.date_operation',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('invoices.type_facture','=','credit')->where('invoices.enterprise_id','=',$ese['id'])->get(['invoices.*','D.sold']);
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
                 $entries=OtherEntries::where('other_entries.user_id','=',$userId)->join('users as U','other_entries.user_id','=','U.id')->leftjoin('accounts as AC','other_entries.account_id','=','AC.id')->whereBetween('other_entries.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('other_entries.enterprise_id','=',$ese['id'])->get(['other_entries.*','AC.name as account_name','U.user_name']);
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
                 $expenditures=Expenditures::where('expenditures.user_id','=',$userId)->join('users as U','expenditures.user_id','=','U.id')->leftjoin('accounts as AC','expenditures.account_id','=','AC.id')->whereBetween('expenditures.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('expenditures.enterprise_id','=',$ese['id'])->get(['expenditures.*','AC.name as account_name','U.user_name']);
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
                 $fences=Fences::where('fences.user_id','=',$userId)->join('users as U','fences.user_id','=','U.id')->whereBetween('fences.date_concerned',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('enterprise_id','=',$ese['id'])->get(['fences.*','U.user_name','U.avatar']);
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
                 $debts=Debts::where('debts.created_by_id','=',$userId)->join('invoices as I','debts.invoice_id','=','I.id')->leftjoin('customer_controllers as C','debts.customer_id','=','C.id')->whereBetween('debts.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('I.enterprise_id','=',$ese['id'])->get(['debts.*','C.customerName','I.money_id']);
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
                $payments=DebtPayments::leftjoin('debts as D','debt_payments.debt_id','=','D.id')->join('invoices as I','D.invoice_id','=','I.id')->where('debt_payments.done_by_id','=',$userId)->whereBetween('debt_payments.done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('I.enterprise_id','=',$ese['id'])->get(['debt_payments.*','I.money_id']);
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
                 $cautions=Cautions::whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('enterprise_id','=',$ese['id'])->where('user_id','=',$userId)->get();
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
                         $account_entries=OtherEntries::where('other_entries.user_id','=',$userId)->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('account_id','=',$account['id'])->get();
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
                         $account_expenditures=Expenditures::where('expenditures.user_id','=',$userId)->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('account_id','=',$account['id'])->get();
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
        
        return ['nbrfirstentries'=>$nbrfirstentries,'nbrmembersaccountstovalidate'=>$nbrmembersaccountstovalidate,'message2'=>'dashboard2','totalEntriesCautions'=>$totalEntriesCautions,'totalSellByCautions'=>$totalSellByCautions,'totalSellByBonus'=>$totalSellByBonus,'totalEntriesBonus'=>$totalEntriesBonus,'totalSellBypoints'=>$totalSellBypoints,'totalEntriesPoints'=>$totalEntriesPoints,'total_accounts'=>$total_account_entries+$total_account_expenditures,'default_money'=>$defautmoney,'from'=>$request['from'],'to'=>$request['to'],'message'=>$msg,'total_cash'=>$total_cash,'total_credits'=>$total_credits,'total_entries'=>$total_entries,'total_expenditures'=>$total_expenditures,'total_fences'=>$total_fences,'total_debts'=>$total_debts,'cash'=>$cash,'credits'=>$credits,'expenditures'=>$expenditures,'entries'=>$entries,'fences'=>$fences,'debts'=>$debts,'accounts'=>$accounts];
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
     * Adding new member in the DB
     */
    public function newmember(Request $request){
         //test if exists new user
         $ifexists = user::join('usersenterprises as E','users.id','E.user_id')
         ->where('user_name',$request['user_name'])
         ->where('E.enterprise_id',$request->enterprise_id)->first();
        if($ifexists){
        return response()->json([
        "status"=>400,
        "message"=>"duplicated",
        "error"=>null,
        "data"=>null
        ]);
        }else{
        // DB::beginTransaction();
        try {
        if(!$request['uuid']){
            $request['uuid']=$this->getUuId('M','C');
        }

        $request['user_password']="wekaakiba-0123456789";
        $request['status']="enabled";
        $newuser = user::create($request->all());
        if($newuser){
            usersenterprise::create([
                'user_id'=>$newuser->id,
                'enterprise_id'=>$request->enterprise_id
            ]);

            //create user as customer
            $ascustomer = CustomerController::create([
                    'created_by_id'=>$request->user_id,
                    'customerName'=>$newuser->user_name,
                    'phone'=>$newuser->user_phone,
                    'mail'=>$newuser->user_mail,
                    'type'=>'physique',
                    'enterprise_id'=>$request->enterprise_id,
                    'uuid'=>$newuser->uuid,
                    'member_id'=>$newuser->id
                ]);

            $cdf=moneys::where('enterprise_id',$request->enterprise_id)->where('abreviation','CDF')->first();
            $usd=moneys::where('enterprise_id',$request->enterprise_id)->where('abreviation','USD')->first();

            $cdfFund=funds::create([
                'sold'=>0,
                'description'=>'Compte '.$newuser->user_name.' CDF',
                'money_id'=>$cdf->id,
                'user_id'=>$newuser->id,
                'enterprise_id'=>$request->enterprise_id
            ]);  
            
            $usdFund=funds::create([
                'sold'=>0,
                'description'=>'Compte '.$newuser->user_name.' USD',
                'money_id'=>$usd->id,
                'user_id'=>$newuser->id,
                'enterprise_id'=>$request->enterprise_id
            ]);

            //funds creation
            if(isset($request['deposit_id']) && !empty($request['deposit_id'])){
                $deposit=DepositController::find($request['deposit_id']);
                if ($deposit) {
                    DepositsUsers::create([
                        'deposit_id'=>$deposit['id'],
                        'user_id'=>$newuser['id'],
                        'level'=>'simple'
                    ]);
                }
            }

            $customerCtrl = new CustomerControllerController();
            return response()->json([
                "status"=>200,
                "message"=>"success",
                "error"=>null,
                "data"=>$customerCtrl->show($ascustomer)
            ]);
        }

        // DB::commit();
        } catch (Exception $th) {
        //    DB::rollBack();
            return response()->json([
                "status"=>500,
                "message"=>"error occured",
                "error"=>$th,
                "data"=>null
            ]);
        }
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
    
    /**
     * Show Weka akiba
     */
    public function showweka(User $user)
    {
        $accountctrl = new WekamemberaccountsController();
        $member=$this->show(user::find($user->id));
        $member['accounts']=$accountctrl->allaccounts($member['id']);
        return $member;
    }

    public function getone($id){

        return User::leftjoin('affectation_users as A', 'users.id','=','A.user_id')
        ->leftjoin('departments as D', 'A.department_id','=','D.id')
        ->where('users.id', '=',$id)
        ->get(['D.department_name as department_name', 'D.id as department_id', 'users.*', 'A.level'])[0];

    }
    
    public function getuserbyId($id){
        $userfind=User::find($id);
        return $this->show($userfind);
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
    
    public function updatewekamember(Request $request, $id)
    {
        $user=User::find($id);
        $user->update($request->all());

       return $this->showweka($user);
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
        return ['message'=>$message,'user'=>$user,'enterprise'=>$actualEse,'defaultmoney'=>$this->defaultmoney($actualEse['id'])];
    }

    /**
     * WEKA AKIBA METHODS
     */

     public function wekamemberslist($enterprise_id){
        $list=usersenterprise::join('users','usersenterprises.user_id','users.id')
        ->where('usersenterprises.enterprise_id','=',$enterprise_id)
        ->where('users.user_type','=','member')
        ->paginate(20);
        $listdata=$list->getCollection()->transform(function ($item){
            $accountctrl = new WekamemberaccountsController();
            $member=$this->show(user::find($item['user_id']));
            $member['accounts']=$accountctrl->allaccounts($member['id']);
            return $member;
        });
        return $listdata;
     }

     /**
      * list des membres weka akiba groupes
      */
     public function wekamemberslistpaginated($enterprise_id){

        $list=usersenterprise::join('users','usersenterprises.user_id','users.id')
        ->where('usersenterprises.enterprise_id','=',$enterprise_id)
        ->where('users.user_type','=','member')
        ->paginate(20);
        
        $list->getCollection()->transform(function ($item){
            $accountctrl = new WekamemberaccountsController();
            $member=$this->show(user::find($item['user_id']));
            $member['accounts']=$accountctrl->allaccounts($member['id']);
            return $member;
        });
        return $list;
     }
     
     /**
      * WEKA AKIBA MEMBERS TO VALIDATED
      */
      public function wekamemberstovalidate($enterprise_id){
        $list=usersenterprise::join('users','usersenterprises.user_id','users.id')
        ->where('usersenterprises.enterprise_id','=',$enterprise_id)
        ->where('users.user_type','=','member')
        ->where('users.status','disabled')
        ->paginate(20);
        $listdata=$list->getCollection()->transform(function ($item){
            $accountctrl = new WekamemberaccountsController();
            $member=$this->show(user::find($item['user_id']));
            $member['accounts']=$accountctrl->allaccounts($member['id']);
            return $member;
        });
        return $listdata;
      }
     
     public function agentsearch(Request $request){

        if($request->keyword && !empty($request->keyword)){
            $list=collect(usersenterprise::join('users','usersenterprises.user_id','users.id')
            ->where('usersenterprises.enterprise_id','=',$request['enterprise_id'])
            ->where('users.full_name','LIKE',"%$request->keyword%")
            ->orWhere('users.uuid','LIKE',"%$request->keyword%")
            ->orWhere('users.user_name','LIKE',"%$request->keyword%")
            ->limit(10)
            ->get('usersenterprises.*'));
        
            $listdata=$list->map(function ($item){
                return $this->show(user::find($item['user_id']));
            });
            return $listdata;
        }else{
            return [];
        }
      
     }
     
     public function wekamemberslookup(Request $request){

        if($request->keyword && !empty($request->keyword)){
            $list=collect(usersenterprise::join('users','usersenterprises.user_id','users.id')
            // ->leftjoin('wekamemberaccounts','usersenterprises.user_id','=','wekamemberaccounts.user_id')
            ->where('usersenterprises.enterprise_id','=',$request['enterprise_id'])
            // ->where('users.user_type','=','member')
            ->where('users.full_name','LIKE',"%$request->keyword%")
            ->orWhere('users.uuid','LIKE',"%$request->keyword%")
            ->limit(10)
            ->get('usersenterprises.*'));
        
            $listdata=$list->map(function ($item){
                return $this->showweka(user::find($item['user_id']));
            });
            return $listdata;
        }else{
            return [];
        }
      
     } 
     
     public function wekasearchmembersbywords(Request $request){

        $list=user::join('users','usersenterprises.user_id','users.id')
        ->where('enterprise_id','=',$request['enterpriseid'])
        ->where('full_name','LIKE',"%$request->word%")
        ->orWhere('id','=',"$request->word")
        ->orWhere('uuid','LIKE',"%$request->word%")
        ->limit(20)->get('users.*');

        return $list;
     }

     public function newwekamember(Request $request){
        $customerctrl= new CustomerControllerController();
        $actualuser=$this->getinfosuser($request['created_by_id']);
      
        if($actualuser){
            $actualese=$this->getEse($actualuser->id);
            if($actualese){
                 //test if exists new user
            $ifexists = user::join('usersenterprises as E','users.id','E.user_id')
            ->where('full_name','=',$request['full_name'])
            ->where('E.enterprise_id',$actualese->id)->first();
                if($ifexists){
                    return response()->json([
                        "status"=>500,
                        "message"=>"error",
                        "error"=>"duplicated member",
                        "data"=>$ifexists
                    ]); 
                }else{
                    try {
                        $request['uuid']="GOM".date('Y').$this->EseNumberUsers($actualese->id);
                        $request['user_name']="member".$this->EseNumberUsers($actualese->id);
                        $request['user_password']="member".date('his').$this->EseNumberUsers($actualese->id);
                        $request['status']="enabled";
                        $newuser = user::create($request->all());
                        if($newuser){
                            usersenterprise::create([
                                'user_id'=>$newuser->id,
                                'enterprise_id'=>$actualese->id
                            ]);
        
                            //create user as customer
                            $ascustomer = CustomerController::create([
                                    'created_by_id'=>$actualuser->id,
                                    'customerName'=>$newuser->full_name,
                                    'phone'=>$newuser->user_phone,
                                    'mail'=>$newuser->user_mail,
                                    'type'=>'physique',
                                    'enterprise_id'=>$actualese->id,
                                    'uuid'=>$newuser->uuid,
                                    'member_id'=>$newuser->id
                                ]);
        
                            $cdf=moneys::where('enterprise_id',$actualese->id)->where('abreviation','CDF')->first();
                            $usd=moneys::where('enterprise_id',$actualese->id)->where('abreviation','USD')->first();
        
                            $cdfaccount=wekamemberaccounts::create([
                                'sold'=>$request['soldecdf']?$request['soldecdf']:0,
                                'description'=>'Compte '.$newuser->full_name.' CDF',
                                'money_id'=>$cdf->id,
                                'user_id'=>$newuser->id,
                                'enterprise_id'=>$actualese->id,
                                'account_number'=>"CP".date('Y').$this->EseNumberAccounts($actualese->id),
                                'account_status'=>"enabled"
                            ]);  
                            
                            $usdaccount=wekamemberaccounts::create([
                                'sold'=>$request['soldeusd']?$request['soldeusd']:0,
                                'description'=>'Compte '.$newuser->full_name.' USD',
                                'money_id'=>$usd->id,
                                'user_id'=>$newuser->id,
                                'enterprise_id'=>$actualese->id,
                                'account_number'=>"CP".date('Y').$this->EseNumberAccounts($actualese->id),
                                'account_status'=>"enabled"
                            ]);
                        }

                      if($request['returned'] && $request['returned']==='customer'){
                        $datatoreturn=$customerctrl->show($ascustomer);
                      }else{
                        $datatoreturn=$this->showweka($newuser);
                      }  
                    return response()->json([
                        "status"=>200,
                        "message"=>"success",
                        "error"=>null,
                        "data"=>$datatoreturn
                    ]);

                } catch (Exception $e) {
                    return response()->json([
                        "status"=>500,
                        "message"=>"error",
                        "error"=>$e->getMessage(),
                        "data"=>null
                    ]); 
                } 
                }
               
            }else{
                return response()->json([
                    "status"=>402,
                    "message"=>"error",
                    "error"=>"unknown enterprise",
                    "data"=>null
                ]); 
            }
        }else{
            return response()->json([
                "status"=>402,
                "message"=>"error",
                "error"=>"unknown user",
                "data"=>null
            ]);
        }
     }
     public function wekaimportmembers(Request $request){
        $actualuser=$this->getinfosuser($request->data['sentby']);
        $actualese=$this->getEse($actualuser->id);
        $allmembers=[];

        foreach ($request->data['members'] as $key=> $membersent) {
            $membersentupdated['note']="member";
            $membersentupdated['status']="enabled";
            $membersentupdated['user_type']="member";
            $membersentupdated['user_password']="member".date('his').$key;
            $membersentupdated['user_phone']=$membersent['phone']?$membersent['phone']:"";
            $membersentupdated['user_mail']=$membersent['email']?$membersent['email']:"";
            $membersentupdated['user_name']="member".date('his').$key;
            $membersentupdated['full_name']=$membersent['fullname']?$membersent['fullname']:"";
            $membersentupdated['uuid']=$membersent['uuid']?$membersent['uuid']:"";

            //test if exists new user
            $ifexists = user::join('usersenterprises as E','users.id','E.user_id')
            ->where('full_name',$membersentupdated['full_name'])
            ->where('E.enterprise_id',$actualese->id)->first();
        if($ifexists){
            
        }else{
            $newuser = user::create($membersentupdated);
                if($newuser){
                    usersenterprise::create([
                        'user_id'=>$newuser->id,
                        'enterprise_id'=>$actualese->id
                    ]);

                    //create user as customer
                    $ascustomer = CustomerController::create([
                            'created_by_id'=>$actualuser->id,
                            'customerName'=>$newuser->full_name,
                            'phone'=>$newuser->user_phone,
                            'mail'=>$newuser->user_mail,
                            'type'=>'physique',
                            'enterprise_id'=>$actualese->id,
                            'uuid'=>$newuser->uuid,
                            'member_id'=>$newuser->id
                        ]);

                    $cdf=moneys::where('enterprise_id',$actualese->id)->where('abreviation','CDF')->first();
                    $usd=moneys::where('enterprise_id',$actualese->id)->where('abreviation','USD')->first();

                    $cdfaccount=wekamemberaccounts::create([
                        'sold'=>$membersent['soldecdf']?$membersent['soldecdf']:0,
                        'description'=>'Compte '.$newuser->full_name.' CDF',
                        'money_id'=>$cdf->id,
                        'user_id'=>$newuser->id,
                        'enterprise_id'=>$actualese->id,
                        'account_status'=>"enabled",
                        'account_number'=>"CP".date('Y').$this->EseNumberAccounts($actualese->id)
                    ]);  
                    
                    $usdaccount=wekamemberaccounts::create([
                        'sold'=>$membersent['soldeusd']?$membersent['soldeusd']:0,
                        'description'=>'Compte '.$newuser->full_name.' USD',
                        'money_id'=>$usd->id,
                        'user_id'=>$newuser->id,
                        'enterprise_id'=>$actualese->id,
                        'account_status'=>"enabled",
                        'account_number'=>"CP".date('Y').$this->EseNumberAccounts($actualese->id)
                    ]);
                }
                array_push($allmembers,$membersentupdated);
            }
        }

        return $allmembers;
     }

     public function usersbytypes(Request $request){
        $listdata=[];
        if($request['usertype']=="collectors"){
            $list=usersenterprise::join('users','usersenterprises.user_id','users.id')
            ->where('usersenterprises.enterprise_id','=',$request['enterprise_id'])
            ->where('users.collector','=',true)
            ->paginate(20);
            $listdata=$list->getCollection()->map(function ($item){
                $accountctrl = new WekamemberaccountsController();
                $member=$this->show(user::find($item['user_id']));
                $member['accounts']=$accountctrl->allaccounts($member['id']);
                return $member;
            });
        }
    
        return $listdata;
     }

     public function membertocollectors(Request $request){
        if($request['members'] && count($request['members'])>0){
            try{
                $succeded=[];
                foreach($request['members'] as $member){
                    $memberinfos=user::find($member['id']);
                    if($memberinfos){
                        $memberinfos['collector']=true;
                        $memberinfos->save();

                        array_push($succeded,$this->show($memberinfos));
                    }
                    return response()->json([
                        "status"=>200,
                        "message"=>"success",
                        "error"=>null,
                        "data"=>$succeded
                    ]);
                }
            }catch(Exception $e){
                return response()->json([
                    "status"=>500,
                    "message"=>"error",
                    "error"=>$e->getMessage(),
                    "data"=>null
                ]); 
            }
        }else{
            return response()->json([
                "status"=>402,
                "message"=>"error",
                "error"=>"members not sent",
                "data"=>null
            ]);
        }
     
     }
}
