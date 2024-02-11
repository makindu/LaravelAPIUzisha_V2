<?php

namespace App\Http\Controllers;

use App\Models\funds;
use Illuminate\Http\Request;
use App\Models\requestHistory;
use App\Http\Requests\StorefundsRequest;
use App\Http\Requests\UpdatefundsRequest;
use Illuminate\Support\Facades\DB;

class FundsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list= funds::leftjoin('users as U', 'funds.user_id','=','U.id')
        ->leftjoin('moneys as M', 'funds.money_id','=','M.id')
        ->get(['M.abreviation as money_abreviation', 'U.user_name', 'funds.*']);
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $fund=funds::create($request->all());
        //make a new entry
        if($fund->sold>0){
            requestHistory::create(['user_id'=>$request->created_by,'fund_id'=>$fund->id,'amount'=>$fund->sold,'motif'=>'Premier approvisionnement','type'=>'entry']);
        }
       
        return funds::leftjoin('users as U', 'funds.user_id','=','U.id')
        ->leftjoin('moneys as M', 'funds.money_id','=','M.id')
        ->where('funds.id','=',$fund->id)
        ->get(['M.abreviation as money_abreviation', 'U.user_name', 'funds.*'])[0];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\funds  $funds
     * @return \Illuminate\Http\Response
     */
    public function show(funds $funds)
    {
        return funds::leftjoin('users as U', 'funds.user_id','=','U.id')
        ->leftjoin('moneys as M', 'funds.money_id','=','M.id')
        ->where('funds.id','=',$funds->id)
        ->get(['M.abreviation as money_abreviation', 'U.user_name', 'funds.*'])[0];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\funds  $funds
     * @return \Illuminate\Http\Response
     */
    public function edit(funds $funds)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatefundsRequest  $request
     * @param  \App\Models\funds  $funds
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatefundsRequest $request, funds $funds)
    {
        $element = funds::find($funds);
        return $element->update($request->all());
    }

    /**
     * Update a specific fund
     */
    public function update2(Request $request,$funds){
        $element = funds::find($funds);
        $element->update($request->all());
        return $this->show($element);
    }

    /**
     * Reset a specific fund
     */
    public function reset($id){
        
        DB::update('update funds set sold=0 where id =? ',[$id]);
        $tub=funds::find($id);
        DB::delete('delete from request_histories where fund_id=?',[$id]);
        return $this->show($tub);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\funds  $funds
     * @return \Illuminate\Http\Response
     */
    public function destroy(funds $funds)
    {
        return funds::destroy($funds);
    }

    /**
     * Remove the specified resource from storage by forcing
     */
    public function destroy2($id){
        $funds=funds::find($id);
        $funds->delete();
    }

    /**
     * getting a specific resource in using the Id
     */
    public function getByid($id) {
        
        $data = funds::find($id);
        if(is_null($data)) {
            return response()->json(['message' => 'Data not found'], 200);
        }
        return response()->json($data::find($id), 200);
    }

}
