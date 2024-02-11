<?php

namespace App\Http\Controllers;

use App\Models\PointOfSale;
use App\Http\Requests\StorePointOfSaleRequest;
use App\Http\Requests\UpdatePointOfSaleRequest;
use App\Models\posdeposits;
use App\Models\User;
use App\Models\UsersPointOfSale;
use Illuminate\Http\Request;

class PointOfSaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return PointOfSale::all();
    }

    public function foraspecificEse($ese){
        return PointOfSale::where('enterprise_id','=',$ese)->get();
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
     * @param  \App\Http\Requests\StorePointOfSaleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePointOfSaleRequest $request)
    {
        $request['uuid']=$this->getUuId('C','POS');
        $msg="error";
        $user=$this->getinfosuser($request['user_id']);
        $ese=$this->getEse($request['user_id']);
        if($user && $ese){
            $msg="success";
            return ['message'=>$msg,'pos'=>PointOfSale::create($request->all())];
        }else{
            return ['message'=>$msg,'pos'=>null];
        }
       
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PointOfSale  $pointOfSale
     * @return \Illuminate\Http\Response
     */
    public function show(PointOfSale $pointOfSale)
    {
        return PointOfSale::join('deposit_controllers as D','posdeposits.deposit_id','=','D.id')->where('posdeposits.id',$pointOfSale['id'])->get();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PointOfSale  $pointOfSale
     * @return \Illuminate\Http\Response
     */
    public function edit(PointOfSale $pointOfSale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePointOfSaleRequest  $request
     * @param  \App\Models\PointOfSale  $pointOfSale
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePointOfSaleRequest $request, PointOfSale $pointOfSale)
    {
        return $pointOfSale->update($request->all());
    }

    public function update2(Request $request, $posId){

        $getter=PointOfSale::find($request['id']);
        $getter->update($request->all());
        return PointOfSale::find($request['id']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PointOfSale  $pointOfSale
     * @return \Illuminate\Http\Response
     */
    public function destroy(PointOfSale $pointOfSale)
    {
        return PointOfSale::destroy($pointOfSale);
    }

    /**
     * affect deposits to the POS
     */
    public function affectDeposits(Request $request){
        $deposits=[];
        $posdepostCtrl= new PosdepositsController();
        if (isset($request['deposits']) && !empty($request['deposits']) && count($request['deposits'])>0) {
            foreach ($request['deposits'] as $value) {
                $ifexists=posdeposits::where('deposit_id',$value['deposit_id'])->where('pos_id',$value['pos_id'])->get();
                if (count($ifexists)<=0){
                   array_push($deposits,$posdepostCtrl->show(posdeposits::create($value)));
                }  
            }
        } 
        return $deposits;
    }
    /**
     * get deposits for a specific pos
     */
    public function getdeposits($posid){
        return posdeposits::join('deposit_controllers as D','posdeposits.deposit_id','=','D.id')->where('posdeposits.pos_id',$posid)->get(['D.*','posdeposits.pos_id','posdeposits.id as pos_affection_id']);
    }

    /**
     * delete deposit to POS 
     */
    public function deleteposit($depositPosId){
        $find = posdeposits::find($depositPosId);
        return $find->delete();
    }

    /**
     * get affected agents
     */
    public function getagents($posId){

        $list =collect(UsersPointOfSale::where('pos_id','=',$posId)->get());
        $users=$list->map(function ($item,$key){
            $userCtrl = new UsersController();
            return $userCtrl->show(User::find($item['user_id']));
        });
        return $users;
    }
    /**
     * delete user to POS
     */
    public function deleteuser(Request $request){
        $find=UsersPointOfSale::where('user_id','=',$request['user_id'])->where('pos_id','=',$request['pos_id'])->first();
        return $find->delete();
    }

    public function destroy2($posid)
    {
        $find=PointOfSale::find($posid);
        return $find->delete($find);
    }
}
