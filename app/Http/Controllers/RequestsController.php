<?php

namespace App\Http\Controllers;

use App\Models\requests;
use App\Models\decision_team;
use App\Models\decision_decisionteam;
use App\Models\decision_chiefdepartments;
use App\Http\Requests\StorerequestsRequest;
use App\Http\Requests\UpdaterequestsRequest;
use App\Models\affectation_users;
use App\Models\moneys;
use App\Models\nbrdecisionteam_validation;
use App\Models\request_files;
use App\Models\request_served;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list=collect(requests::all());
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorerequestsRequest $request)
    {

        // get a difault money
        $defaulteMony = DB::table('moneys')
        ->where('principal', '=', 1)
        ->get();
        $defaulte = $defaulteMony[0];
        $defaultMoneyId = $defaulte->id;

        // get if user affectation exist
        $userId = $request['user_id'];
        $money = $request['request_money'];
        $rate = $request['rate'];
        if (isset($money)==='') {
            $request['request_money'] = $defaultMoneyId;
        }
        if(isset($rate)===''){
            $request['rate'] = 1;
        }

        $data = User::find($userId);
        if(is_null($data)) {
            return response()->json(['message' => 'Daata not found'], 200);
        }
        $data::find($userId)->join('affectation_users', 'users.id','=','affectation_users.user_id')
        ->find($userId, ['*']);

        if (is_null($data)){
            return response()->json(['message' => 'Veuillez contacter votre administrateur pour votre affectation'], 500);
        }else{
            $seved = requests::create($request->all());
            $dataSevedStructu = $this->getByid($seved['id']);
            $message = 'added';
            return response()->json($dataSevedStructu);
        }

    }

    public function requestvalidation(Request $request,$userId){

        //check who if chiefdepart
        $ifchiefdepart=affectation_users::where('user_id','=',$userId)->get();
        $ifdecision=decision_team::where('user_id','=',$userId)->get();

        if(count($ifchiefdepart)>0){
            if($ifchiefdepart[0]['level']==='chief'){
                //if already validate by decision team
                $ifvalidatebydecision=decision_decisionteam::where('request_id','=',$request->id)->get();
                if(count($ifvalidatebydecision)>0){
                    //sent msg impossible
                    $msg='unauthorized';
                }else{
                    //if already validate by chief depart updating decision
                    $ifvalidatebychiefdepart=decision_chiefdepartments::where('request_id','=',$request->id)->get();
                    if(count($ifvalidatebychiefdepart)>0){
                        //update decision
                        $funded=$ifvalidatebychiefdepart[0];
                        $funded->update([
                            'response'=>$request['response'],
                            'user_id'=>$userId,
                            'request_id'=>$request->id
                        ]);
                        //update request
                            //test the decision of the chief
                            if($request->response===1){
                                //if validated
                                $request['status']='decision';
                                $request['validatechiefdepart']=true;
                                $this->updaterequest2($request, $request->id);
                                $msg='updated';
                            }else if($request->response===0){
                                //if invalidated
                                $request['status']='chefdepart';
                                $request['validatechiefdepart']=false;
                                $this->updaterequest2($request, $request->id);
                                $msg='updated';
                            }else{
                                $msg='unauthorized';
                            }
                    }else{
                        //creating new validation for chief depart
                        if($request->response===1){
                            //if validated
                            decision_chiefdepartments::create($request->all());
                            $request['status']='decision';
                            $request['validatechiefdepart']=true;
                            $this->updaterequest2($request, $request->id);
                            $msg='newdecision';
                        }else if($request->response===0){
                            //if invalidated
                            decision_chiefdepartments::create([
                                'response'=>$request['response'],
                                'user_id'=>$userId,
                                'request_id'=>$request->id
                            ]);
                            $request['status']='chefdepart';
                            $request['validatechiefdepart']=false;
                            $this->updaterequest2($request, $request->id);
                            $msg='newdecision';
                        }else{
                            $msg='unauthorized';
                        }
                    }
                }
            }
        }else if(count($ifdecision)>0){
            //check if the member has authorized to validate
            $canvalidate=decision_team::where('user_id','=',$userId)->where('access','=','rw')->get();
            if(count($canvalidate)>0){
                    //check if already validate by chef depart and it's true
                    $ifvalidatebychiefdepart=decision_chiefdepartments::where('request_id','=',$request->id)->where('response','=','1')->get();
                    if(count($ifvalidatebychiefdepart)>0){
                            //check if already disbursed
                            $ifdisturbed=request_served::where('request_id','=',$request->id)->get();
                            if(count($ifdisturbed)>0){
                                //no possibility to validate because already disbursed
                                $msg='unauthorized';
                            }else{
                                //check if the number of validations is reached
                                $nbrvalidation=nbrdecisionteam_validation::all();

                                //getting nbr of members already validate
                                $alreadyvalidate=decision_decisionteam::join('decision_teams','decision_decisionteams.user_id','=','decision_teams.user_id')
                                ->where('decision_decisionteams.request_id','=',$request->id)
                                ->where('decision_decisionteams.response','=','1')
                                ->where('decision_teams.access','=','rw')->get();

                                if($nbrvalidation[0]['nbr']==count($alreadyvalidate)){
                                    //impossible to validate because already validated
                                    $msg='unauthorized';
                                }
                                else if($nbrvalidation[0]['nbr']>count($alreadyvalidate)){
                                        //if the user has already validate :update the validation
                                        $hashevalidate=decision_decisionteam::where('request_id','=',$request->id)->where('user_id','=',$userId)->get();
                                        if(count($hashevalidate)>0){
                                            //update the decision
                                            $hashevalidate[0]->update([
                                                'response'=>$request['response'],
                                                'user_id'=>$userId,
                                                'request_id'=>$request->id
                                            ]);
                                            //update request
                                            $request['status']='decision';
                                            $this->updaterequest2($request, $request->id);
                                            $msg='updated';
                                            //check if the request can be disbursed again after a new validation
                                            $this->canbedisbursed($request,$request->id);

                                        }else{
                                            //making new validation
                                            decision_decisionteam::create([
                                                'response'=>$request['response'],
                                                'user_id'=>$userId,
                                                'request_id'=>$request->id
                                            ]);
                                            //update request
                                            $request['status']='decision';
                                            $this->updaterequest2($request, $request->id);
                                            $msg='newdecision';
                                            //check if the request can be disbursed again after a new validation
                                            $this->canbedisbursed($request,$request->id);
                                        }
                                
                                }else{
                                    $msg="unauthorized";
                                }
                            }
                    }else{
                        $msg="unauthorized";
                    }
            }else{
                $msg="unauthorized";
            }
        }else{
            $msg="unauthorized";
        }
       return ['request'=>$this->show(requests::find($request->id)),'message'=>$msg];  
    }

    /**
     * Check if the request can be disbursed 
     */
    public function canbedisbursed($request,$requestid){

         //check if the number of validations is reached
         $nbrvalidation=nbrdecisionteam_validation::all();

         //getting nbr of members already validate
         $alreadyvalidate=decision_decisionteam::join('decision_teams','decision_decisionteams.user_id','=','decision_teams.user_id')
         ->where('decision_decisionteams.request_id','=',$requestid)
         ->where('decision_decisionteams.response','=','1')
         ->where('decision_teams.access','=','rw')->get();

         if($nbrvalidation[0]['nbr']==count($alreadyvalidate)){
               //update request
               if($request->response===0){
                $request['status']='invalidated';
                $request['validatedecisionteam']=false;
               }else if($request->response===1){
                $request['status']='toserve';
                $request['validatedecisionteam']=true;
               }
             
               $this->updaterequest2($request, $requestid);
         }
    }

    /**
     * private method for internal updating of a request
     */
    private function updaterequest2(Request $request,$id){
        $dataSelect = requests::find($id);
        if(is_null($dataSelect)) {
            return response()->json(['message' => 'Data_not_found'], 404);
        }else{
            $dataSelect->update($request->all());
            return response()->json(['message'=>'updated']);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\requests  $requests
     * @return \Illuminate\Http\Response
     */
    public function show(requests $requests)
    {
        return requests::leftjoin('departments as D', 'requests.department_id','=','D.id')
        ->leftjoin('users as U', 'requests.user_id','=','U.id')
        ->leftjoin('moneys as M', 'requests.request_money','=','M.id')
        ->where('requests.id','=',$requests->id)
        ->get(['D.department_name','M.abreviation as money_abreviation', 'U.user_name', 'requests.*'])[0];
    }

    //to validate by chiefdepart
    public function tovalidatebychiefdepart(Request $request){

        $requests=requests::leftjoin('users as U', 'requests.user_id','=','U.id')
        ->leftjoin('departments as D', 'requests.department_id','=','D.id')
        ->leftjoin('moneys as M', 'requests.request_money','=','M.id')
        ->where('requests.department_id','=',$request->departid)
        ->where('requests.status','=','created')
        ->where('requests.type','<>','draft')
        ->where('requests.user_id','<>',$request->user_id)
        ->get(['D.department_name','M.abreviation as money_abreviation', 'U.user_name', 'requests.*']);

        return $requests; 
    }
    
    //to validate by decision team
    public function tovalidatebydecisionteam(Request $request){

        $requests=requests::leftjoin('users as U', 'requests.user_id','=','U.id')
        ->leftjoin('departments as D', 'requests.department_id','=','D.id')
        ->leftjoin('moneys as M', 'requests.request_money','=','M.id')
        ->where('requests.status','=','decision')
        ->where('requests.user_id','<>',$request->user_id)
        ->get(['D.department_name','M.abreviation as money_abreviation', 'U.user_name', 'requests.*']);

        return $requests; 
    }
  
    //get validated request by chiefdepart for user
    public function validatedbychiefdepart($userid){
        $requests=requests::join('decision_chiefdepartments','requests.id','decision_chiefdepartments.request_id')->where('requests.user_id','=',$userid)->where('decision_chiefdepartments.response','=','1')->get();
        return $requests;   
    }

    //getunvalidated request by user
    public function unvalidatedbychiefdepart($userid){
        $requests=requests::join('decision_chiefdepartments','requests.id','decision_chiefdepartments.request_id')->where('requests.user_id','=',$userid)->where('decision_chiefdepartments.response','=','0')->get();
        return $requests;   
    }
    
    //get validated requests by decision team for user
    public function validatedbydecisionteam($userid){
        $requests=requests::where('user_id','=',$userid)->where('status','=','toserve')->get();     
        return $requests;   
    } 
    
    //validated by decision team all
    public function validatedbydecisionteamall(){
        $requests=requests::where('status','=','toserve')->get();     
        return $requests;
    }
    //get validated requests by decision team for user
    public function unvalidatedbydecisionteam($userid){

        $requests=requests::where('user_id','=',$userid)->where('status','=','notbeserved')->get();     
        return $requests;   
    }

    //get to be served
    public function tobeserved($userid){

        $requests=requests::where('user_id','=',$userid)->where('status','=','toserve')->get();     
        return $requests;   
    }  
    
    //tobeservedall
    public function tobeservedall(){

        $list=collect(requests::where('status','=','toserve')
        ->orwhere('status','=','partially_served')
        ->get());

        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });

        return $listdata;
    }

    //get already served
    public function alreadyserved($userid){

        $list=collect(requests::where('user_id','=',$userid)->where('status','=','served')->get());

        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        
        return $listdata;  
    }

    //already served all 
    public function alreadyservedall(){

        $list=collect(requests::where('status','=','served')->get());

        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        
        return $listdata; 
 
    }

    //get all requests for a specific user
    public function byuser($userid){
        $requests=requests::leftjoin('users as U', 'requests.user_id','=','U.id')
        ->leftjoin('departments as D', 'requests.department_id','=','D.id')
        ->leftjoin('moneys as M', 'requests.request_money','=','M.id')
        ->where('requests.user_id','=',$userid)
        ->get(['D.department_name','M.abreviation as money_abreviation', 'U.user_name', 'requests.*']);   
        return $requests; 
    }

    /**
     * get all requests for a specific depart
     */ 
    public function bydepart($departid){

        $list=collect(requests::where('department_id','=',$departid)->get());

        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        
        return $listdata;
    }

    /**
     * get all files for a specific depart
     */
    public function filesbydepart($departid){

        $files=request_files::join('requests','request_files.request_id','requests.id')
        ->where('requests.department_id','=',$departid)->get();     
        return $files; 
    }

    /**
     * get all request served by a specific tub or found
     */
     public function requestservedbytub($idtub){
        $list=request_served::join('requests','request_serveds.request_id','=','requests.id')->where('request_serveds.fund_id','=',$idtub)->get(['request_serveds.*','requests.*']);
        return $list;
     }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\requests  $requests
     * @return \Illuminate\Http\Response
     */
    public function edit(requests $requests)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdaterequestsRequest  $request
     * @param  \App\Models\requests  $requests
     * @return \Illuminate\Http\Response
     */
    public function update(UpdaterequestsRequest $request, requests $requests)
    {
        $element = requests::find($requests);
        $element->update($request->all());
        return $this->show($element);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\requests  $requests
     * @return \Illuminate\Http\Response
     */
    public function destroy(requests $requests)
    {
        return requests::destroy($requests);
    }

    public function getByid($id) {
        $data = requests::find($id);
        if(is_null($data)) {
            return response()->json(['message' => 'Data not found'], 200);
        }
        return response()->json(
            $data::find($id)->leftjoin('departments as D', 'requests.department_id','=','D.id')
            ->leftjoin('users as U', 'requests.user_id','=','U.id')
            ->leftjoin('moneys as M', 'requests.request_money','=','M.id')
            ->find($id, ['D.department_name','M.abreviation as money_abreviation', 'U.user_name', 'requests.*'])
            , 200);
    }

    public function updateRequest(Request $request, $id) {
        $dataSelect = requests::find($id);
        $getIfDecisionExistRequest = DB::table('decision_chiefdepartments')
        ->where('request_id', '=', $id)
        ->get();
        if (count($getIfDecisionExistRequest) > 0) {
            return response()->json(['message'=>'request_validaded']);
        }elseif(is_null($dataSelect)) {
            return response()->json(['message' => 'Data_not_found'], 404);
        }else{
            $dataSelect->update($request->all());
            $dataSelect=$this->show($dataSelect);
            return response()->json(['message'=>'updated','request'=>$dataSelect]);
        }
    }

    public function deleteRequest(Request $request, $id) {
        
        $data=['message'=>''];

        $getIfDecisionExistRequest = DB::table('decision_chiefdepartments')
        ->where('request_id', '=', $id)
        ->get();

        if (count($getIfDecisionExistRequest) > 0) 
        {
            $data['message']='request_validaded';
            return $data;
        }else
        {
            $selectDataDelet = requests::find($id);
            if (is_null($selectDataDelet)) {
                $data['message']='Data_not_found';
                return $data;
            }else{
                $selectDataDelet->delete();
                $data['message']='deleted';
                return $data;
            }
            
        }
    }
}
