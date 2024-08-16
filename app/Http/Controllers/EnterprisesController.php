<?php

namespace App\Http\Controllers;

use Roles;
use App\Models\User;
use App\Models\funds;
use App\Models\moneys;
use App\Models\Accounts;
use App\Models\Enterprises;
use App\Models\PointOfSale;
use App\Models\DepositsUsers;
use App\Models\usersenterprise;
use App\Models\UsersPointOfSale;
use App\Models\DepositController;
use App\Models\DepositsCategories;
use Illuminate\Support\Facades\DB;
use App\Models\Roles as ModelsRoles;
use App\Models\CategoriesCustomerController;
use App\Models\CategoriesServicesController;
use App\Http\Requests\StoreEnterprisesRequest;
use App\Http\Requests\UpdateEnterprisesRequest;
use App\Models\CustomerController;
use App\Models\Debts;
use App\Models\DepositServices;
use App\Models\Expenditures;
use App\Models\Fences;
use App\Models\Invoices;
use App\Models\OtherEntries;
use App\Models\StockHistoryController;
use Illuminate\Http\Request;

class EnterprisesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Enterprises::all();
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
     * @param  \App\Http\Requests\StoreEnterprisesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEnterprisesRequest $request)
    {
        $userCtrl = new UsersController();
        $new=Enterprises::create($request->all());

        if($new){
            //affect owner to it enterprise
            usersenterprise::create([
                'user_id'=>$new->user_id,
                'enterprise_id'=>$new->id
            ]);
            //create role and give it to the owner
            $role=ModelsRoles::create([
                'title'=>$request['rules']['ruleSent']['title'],
                'description'=>$request['rules']['ruleSent']['description'],
                'user_id'=>$new->user_id,
                'enterprise_id'=>$new->id,
                'permissions'=>$request['rules']['ruleSent']['permissions']
            ]);
            //update owner with the new role created
            if($role){
                DB::update('update users set permissions = ? where id = ?',[$role['id'],$new->user_id]);
            }
            //creating a default deposit
            $deposit=DepositController::create([
               'user_id'=>$new->user_id,
               'name'=>'dépôt par defaut',
               'description'=>'aucune',
               'type'=>'group',
               'enterprise_id'=>$new->id
            ]);

            //affect the owner to the default deposit
            DepositsUsers::create([
                'deposit_id'=>$deposit->id,
                'user_id'=>$new->user_id,
                'level'=>'chief'
            ]);
            
            //creating default POS
            $pos=PointOfSale::create([
                'user_id'=>$new->user_id,
                'name'=>$request->name.' POS',
                'description'=>$request->description,
                'type'=>'group',
                'sold'=>0,
                'nb_sales_bonus'=>0,
                'bonus_percentage'=>0,
                'workforce_percent'=>0,
                'enterprise_id'=>$new->id,
                'status'=>'enabled'
            ]);

            //Affect created users to the POS created
            UsersPointOfSale::create([
                'user_id'=>$new->user_id,
                'pos_id'=>$pos->id
            ]);
            if (isset ($request['defaultmoney']) && !empty($request['defaultmoney'])) {
                switch ($request['defaultmoney']=='CDF') {
                    case 'CDF':
                        $principalcdf=1;
                        $principalusd=0;
                        break;
                    case 'USD':
                        $principalusd=1;
                        $principalcdf=0;
                        break;
                    default:
                        $principalusd=1;
                        $principalcdf=0;
                        break;
                }  
            }else{
                $principalusd=1;
                $principalcdf=0;
            }
            //Creating default moneys (CDF & USD)
            $cdf=moneys::create([
                'abreviation'=>'CDF',
                'principal'=>$principalcdf,
                'money_name'=>'Francs Congolais',
                'enterprise_id'=>$new->id
            ]);
            $usd=moneys::create([
                'abreviation'=>'USD',
                'principal'=>$principalusd,
                'money_name'=>'Dollars Américains',
                'enterprise_id'=>$new->id
            ]);

            //Creating default funds foreach money created
            $cdfFund=funds::create([
                'sold'=>0,
                'description'=>'Caisse principale CDF',
                'money_id'=>$cdf->id,
                'user_id'=>$new->user_id,
                'enterprise_id'=>$new->id
            ]);  
            
            $usdFund=funds::create([
                'sold'=>0,
                'description'=>'Caisse principale USD',
                'money_id'=>$usd->id,
                'user_id'=>$new->user_id,
                'enterprise_id'=>$new->id
            ]);

            //creating defaults accounts
            Accounts::create([
                'name'=>'Transport',
                'type'=>'gestion',	
                'description'=>'Frais de transport',
                'user_id'=>$new->user_id,
                'enterprise_id'=>$new->id
            ]);
            Accounts::create([
                'name'=>'Communication',
                'type'=>'gestion',	
                'description'=>'Frais de communication',
                'user_id'=>$new->user_id,
                'enterprise_id'=>$new->id
            ]); 
            Accounts::create([
                'name'=>'Loyer',
                'type'=>'gestion',	
                'description'=>'Frais de loyer',
                'user_id'=>$new->user_id,
                'enterprise_id'=>$new->id
            ]);  
            Accounts::create([
                'name'=>'Courants',
                'type'=>'gestion',	
                'description'=>'Electricité',
                'user_id'=>$new->user_id,
                'enterprise_id'=>$new->id
            ]); 
            Accounts::create([
                'name'=>'Eau',
                'type'=>'gestion',	
                'description'=>'REGIDESO',
                'user_id'=>$new->user_id,
                'enterprise_id'=>$new->id
            ]);  
            Accounts::create([
                'name'=>'Personnel',
                'type'=>'gestion',	
                'description'=>'Salaires du personnel',
                'user_id'=>$new->user_id,
                'enterprise_id'=>$new->id
            ]); 
            Accounts::create([
                'name'=>'Divers',
                'type'=>'gestion',	
                'description'=>'Autres dépenses',
                'user_id'=>$new->user_id,
                'enterprise_id'=>$new->id
            ]); 
            Accounts::create([
                'name'=>'Charges boss',
                'type'=>'gestion',	
                'description'=>'Autres dépenses du boss',
                'user_id'=>$new->user_id,
                'enterprise_id'=>$new->id
            ]);

            //creating defaults services categories
            $categserv1=CategoriesServicesController::create([
                'parent_id'=>0,
                'name'=>"Divers",
                'user_id'=>$new->user_id,
                'description'=>"aucune",
                'type_conservation'=>"",
                'has_vat'=>false,
                'enterprise_id'=>$new->id
            ]);  
            
            $categserv2=CategoriesServicesController::create([
                'parent_id'=>0,
                'name'=>"Articles",
                'user_id'=>$new->user_id,
                'description'=>"aucune",
                'type_conservation'=>"",
                'has_vat'=>false,
                'enterprise_id'=>$new->id
            ]);

            //affect categories to the default deposit
            DepositsCategories::create([
                'category_id'=>$categserv1->id,
                'deposit_id'=>$deposit->id
            ]);  
            
            DepositsCategories::create([
                'category_id'=>$categserv2->id,
                'deposit_id'=>$deposit->id
            ]);

            //creating defaults customers categories
            CategoriesCustomerController::create([
                'name'=>"VIP",
                'description'=>"aucune",
                'discount_applicable'=>false,
                'enterprise_id'=>$new->id,
                'user_id'=>$new->user_id
            ]); 

            CategoriesCustomerController::create([
                'name'=>"Normal",
                'description'=>"aucune",
                'discount_applicable'=>false,
                'enterprise_id'=>$new->id,
                'user_id'=>$new->user_id
            ]);
            
            CategoriesCustomerController::create([
                'name'=>"Anonyme",
                'description'=>"aucune",
                'discount_applicable'=>false,
                'enterprise_id'=>$new->id,
                'user_id'=>$new->user_id
            ]);
        }
        $request['user_name']=$this->getinfosuser($new->user_id)['user_name'];
        $request['user_password']=$this->getinfosuser($new->user_id)['user_password'];
        return $userCtrl->login($request);
    }

    /**
     * Condolidate dates operations with created_at
     */
    public function datesConsolidation(Request $request){
        try {
            $expenditures= DB::update("update expenditures set done_at = created_at where done_at IS NULL");
            $entries= DB::update("update other_entries set done_at = created_at where done_at IS NULL");
            $stock= DB::update("update stock_history_controllers set done_at = created_at where done_at IS NULL");
            $invoices= DB::update("update invoices set date_operation=created_at where date_operation IS NULL ");
            $detailsinvoices= DB::update("update invoice_details set date_operation=created_at where date_operation IS NULL ");
            $debts= DB::update("update debts set done_at=created_at where done_at IS NULL ");
            $payments= DB::update("update debt_payments set done_at=created_at where done_at IS NULL ");
            $pointshistories= DB::update("update customerspointshistories set done_at=created_at where done_at IS NULL ");
            $cautions= DB::update("update cautions set done_at=created_at where done_at IS NULL ");
            $requesthistories= DB::update("update request_histories set done_at=created_at where done_at IS NULL ");
            return response()->json([
                "message"=>"success",
                "data"=>[
                    "expenditures"=>$expenditures,
                    "entries"=>$entries,
                    "stock"=>$stock,
                    "invoices"=>$invoices,
                    "detailsinvoices"=>$detailsinvoices,
                    "debts"=>$debts,
                    "payments"=>$payments,
                    "pointshistories"=>$pointshistories,
                    "cautions"=>$cautions,
                    "requesthistories"=>$requesthistories
                ],
                "error"=>null
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>"success",
                "data"=>null,
                "error"=>$th
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Enterprises  $enterprises
     * @return \Illuminate\Http\Response
     */
    public function show(Enterprises $enterprises)
    {
       return Enterprises::find($enterprises);
    }

    public function getone($enterpriseId){
        return Enterprises::find($enterpriseId);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Enterprises  $enterprises
     * @return \Illuminate\Http\Response
     */
    public function edit(Enterprises $enterprises)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateEnterprisesRequest  $request
     * @param  \App\Models\Enterprises  $enterprises
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEnterprisesRequest $request, Enterprises $enterprises)
    {
       return $enterprises->update($request->all());
    }
    
   /**
    * update Ese  
    */
     public function update2(Request $request, $enterprises)
    {
        $message="not found";
        $find=Enterprises::find($enterprises);
        if ($find) {
           $updated=$find->update($request->all());
           if ($updated) {
            $message="updated";
           }else{
            $message="fail";
           }
        }

      return  response()->json([
            'enterprise'=>Enterprises::find($enterprises),
            'message'=>$message
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Enterprises  $enterprises
     * @return \Illuminate\Http\Response
     */
    public function destroy(Enterprises $enterprises)
    {
       return Enterprises::destroy($enterprises);
    }

    /**
     * reset all data
     */
    public function resetalldata(Request $request){
        $User=$this->getinfosuser($request['user_id']);
        $Ese=$this->getEse($User['id']);

        if ($User && $Ese && $User['user_type']=="super_admin" && $User['status']=="enabled") {
            //delete customers
            $customers=CustomerController::where("enterprise_id","=",$Ese['id'])->get();
            if (count($customers)>0) {
                $delete=CustomerController::where("enterprise_id","=",$Ese['id'])->delete();
            }

            //reset deposit quantities
            $deposit_services=DepositServices::join("deposit_controllers as D","deposit_services.deposit_id","=","D.id")
                                ->where("D.enterprise_id","=",$Ese['id'])->get('deposit_services.*');
            //update quantities
            if (count($deposit_services)>0) {
               $deposit_services->toQuery()->update(['available_qte'=>0]);
            }
           //delete all expenditures
            $expenditures=Expenditures::where("enterprise_id","=",$Ese['id'])->get();
            if (count($expenditures)>0) {
                    $delete=Expenditures::where("enterprise_id","=",$Ese['id'])->delete();
            }
             //Debts
             $debts=Debts::join("invoices as I","debts.invoice_id","=","I.id")->where("I.enterprise_id","=",$Ese['id'])->get();
             if (count($debts)>0) {
                 //delete payments
                 DB::table('debt_payments')->whereIn('debt_id', $debts->pluck('id'))->delete();
                 $delete=Debts::join("invoices as I","debts.invoice_id","=","I.id")->where("I.enterprise_id","=",$Ese['id'])->delete();
             }

        //     //invoices
            $invoices=Invoices::where("enterprise_id","=",$Ese['id'])->get();
            if (count($invoices)>0) {
                    //delete details
                    DB::table('invoice_details')->whereIn('invoice_id', $invoices->pluck('id'))->delete();
                    $delete=Invoices::where("enterprise_id","=",$Ese['id'])->delete();
            }

        //    //other entries
           $entries=OtherEntries::where("enterprise_id","=",$Ese['id'])->get();
           if (count($entries)>0) {
                //delete
                $delete=OtherEntries::where("enterprise_id","=",$Ese['id'])->delete();
            }

        //     //Fences
            $fences=Fences::where("enterprise_id","=",$Ese['id'])->get();
            if (count($fences)>0) {
                 //delete details
                 DB::table('fence_ticketings')->whereIn('fence_id', $fences->pluck('id'))->delete();
                 $delete=Fences::where("enterprise_id","=",$Ese['id'])->delete();
            }

        //     //Stock Histories
            $stockstories=StockHistoryController::where("enterprise_id","=",$Ese['id'])->get();
            if(count($stockstories)>0){
                $delete=StockHistoryController::where("enterprise_id","=",$Ese['id'])->delete();
            }

            return response()->json([
                "status"=>200,
                "message"=>"success"
            ]);    
        }else{
            return response()->json([
                "status"=>404,
                "message"=>"unauthorized user"
            ]);
        }
    }
}
