<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerController;
use App\Http\Requests\StoreCustomerControllerRequest;
use App\Http\Requests\UpdateCustomerControllerRequest;
use stdClass;

class CustomerControllerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseid)
    {
        $list=collect(CustomerController::where('enterprise_id','=',$enterpriseid)->get());
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $listdata;
    }

    public function getcustomerbyId($customerId){
        return $this->show(CustomerController::find($customerId));
    }

     /**
     * searching stock histories by done paginated
     */
    public function searchingcustomersbypagination(Request $request){
        $searchTerm = $request->query('keyword', '');
        $enterpriseId = $request->query('enterprise_id', 0);  
        $actualuser=$this->getinfosuser($request->query('user_id'));
        if ($actualuser) {
                $list =CustomerController::where('enterprise_id', '=', $enterpriseId)
                ->where(function($query) use ($searchTerm) {
                    $query->where('customerName', 'LIKE', "%$searchTerm%")
                    ->orWhere('adress', 'LIKE', "%$searchTerm%")
                    ->orWhere('phone', 'LIKE', "%$searchTerm%")
                    ->orWhere('mail', 'LIKE', "%$searchTerm%")
                    ->orWhere('type', 'LIKE', "%$searchTerm%")
                    ->orWhere('uuid', 'LIKE', "%$searchTerm%");
                })
                ->select('customer_controllers.*')
                ->paginate(10)
                ->appends($request->query());

            $list->getCollection()->transform(function ($item){
                return $this->show($item);
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

    public function anonymous($enterpriseid){
        
        $customer=CustomerController::where('customerName','LIKE',"%anonyme%")->where('enterprise_id','=',$enterpriseid)->get()->first();
        if($customer){
            return $customer;
        }else{
            return CustomerController::where('enterprise_id','=',$enterpriseid)->get()->first();
        }
        
    }
    
    /**
     * search
     */
    
     public function search($enterpriseid){
    
        $list=CustomerController::where('enterprise_id','=',$enterpriseid)->paginate(40);
        $list->getCollection()->transform(function ($item){
            return $this->show($item);
        });
        return $list;
     }

     /**
      * Search by words
      */
      public function searchbywords(Request $request){
    
        $list=CustomerController::where('enterprise_id','=',$request['enterpriseid'])->where('customerName','LIKE',"%$request->word%")->orWhere('id','=',"$request->word")->orWhere('uuid','=',"$request->word")->limit(10)->get();

        return $list;
     }
    /**
     *Getting providers 
     */
    public function providers(){
        $list=collect(CustomerController::where('type','=','provider')->get());
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
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
     * @param  \App\Http\Requests\StoreCustomerControllerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCustomerControllerRequest $request)
    {
        $newcustomer= new stdClass;
        $ese=$this->getEse($request['created_by_id']);
        if (!$request['uuid']) {
            $request['uuid']=$this->getUuId('C','C');
        }

        if(!$request['type']){
            $request['type']="physique";
        }

        $request['sync_status']=true;
        $request['enterprise_id']=$ese->id;
        //if exists actual customer
        $ifexists=CustomerController::where('customerName',$request['customerName'])
                                    ->where('enterprise_id',$ese->id)->first();
        if ($ifexists) {
           return response()->json([
            "message"=>"duplicated",
            "data"=>null,
            "status"=>200
           ]);
        }

        $newcustomer=$this->show(CustomerController::create($request->all()));
        return $newcustomer;
    }

    /**
     * importing data or multiple insert
     */
    public function importation(Request $request){
        $data=[];
        if(count($request->data)>0){
            foreach ($request->data as $customer) {
                if ( $newCustomer=$this->store(new StoreCustomerControllerRequest($customer))) {
                    array_push($data,$newCustomer);
                }
            }
        }

        return $data;
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CustomerController  $customerController
     * @return \Illuminate\Http\Response
     */
    public function show(CustomerController $customerController)
    {
       return CustomerController::leftjoin('categories_customer_controllers as C', 'customer_controllers.category_id','=','C.id')
        ->leftjoin('point_of_sales as P', 'customer_controllers.pos_id','=','P.id')
        ->leftjoin('customer_controllers as C1', 'customer_controllers.employer','=','C1.id')
        ->where('customer_controllers.id', '=', $customerController->id)
        ->get(['customer_controllers.*','C1.customerName as employer_name','P.name as pos_name','C.name as category_name'])[0];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CustomerController  $customerController
     * @return \Illuminate\Http\Response
     */
    public function edit(CustomerController $customerController)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCustomerControllerRequest  $request
     * @param  \App\Models\CustomerController  $customerController
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCustomerControllerRequest $request, CustomerController $customerController)
    {
       return $this->show(customerController::find($customerController->update($request->all())));
    }

    public function update2(Request $request,$id)
    {
        $customer=CustomerController::find($id);
        $customer->update($request->all());
        return $this->show($customer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CustomerController  $customerController
     * @return \Illuminate\Http\Response
     */
    public function destroy(CustomerController $customerController)
    {
        return CustomerController::destroy($customerController);
    }
    
    public function delete($customer){
      
        $message="failed";
        $get=CustomerController::find($customer);
        if ($get->delete()) {
            $message="deleted";
        }

        return ['message'=>$message];
    }

    public function getbyuuid(Request $request){
        return CustomerController::where('uuid','=',$request['uuid'])->get()->first();
    }
   
}
