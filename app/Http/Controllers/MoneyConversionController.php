<?php

namespace App\Http\Controllers;

use App\Models\money_conversion;
use App\Http\Requests\Storemoney_conversionRequest;
use App\Http\Requests\Updatemoney_conversionRequest;
use App\Models\requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MoneyConversionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseId)
    {
        $list=collect(money_conversion::where('enterprise_id','=',$enterpriseId)->get());
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
    public function store(Request $request)
    {

        $ifIsExist = $this->getOn($request);
        if (count($ifIsExist) == 0) {
            $data =  money_conversion::create($request->all());
            $dataStrict = $this->getByid($data['id']);
            return response()->json($dataStrict , 200);
        }else{
            return response()->json(['message' => 'La conversion existe deja']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(money_conversion $money_conversion)
    {
        return money_conversion::join('moneys', 'money_conversions.money_id1','=','moneys.id')
        ->join('moneys as M', 'money_conversions.money_id2','=','M.id')
        ->where('money_conversions.id','=',$money_conversion['id'])
        ->get(['moneys.abreviation  as abreviation1',
        'moneys.money_name  as name1',
        'M.abreviation  as abreviation2',
        'M.money_name  as name2','money_conversions.*'])[0];
    }

    public function getOn($request){
        return DB::table('money_conversions')
        ->where('money_id1', '=', $request->money_id1)
        ->where('money_id2', '=', $request->money_id2)
        ->get();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\money_conversion  $money_conversion
     * @return \Illuminate\Http\Response
     */
    public function edit(money_conversion $money_conversion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Updatemoney_conversionRequest  $request
     * @param  \App\Models\money_conversion  $money_conversion
     * @return \Illuminate\Http\Response
     */
    public function update(Updatemoney_conversionRequest $request, money_conversion $money_conversion)
    {
        $element = money_conversion::find($money_conversion);
        return $element->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\money_conversion  $money_conversion
     * @return \Illuminate\Http\Response
     */
    public function destroy(money_conversion $money_conversion)
    {
        return money_conversion::destroy($money_conversion);
    }

    public function getByid($id) {
        $data = money_conversion::find($id);
        if(is_null($data)) {
            return response()->json(['message' => 'Data not found'], 200);
        }
        return response()->json(
            $data::find($id)->join('moneys', 'money_conversions.money_id1','=','moneys.id')
            ->join('moneys as M', 'money_conversions.money_id2','=','M.id')
            ->find($id, ['moneys.abreviation  as abreviation1',
            'moneys.money_name  as name1',
            'M.abreviation  as abreviation2',
            'M.money_name  as name2','money_conversions.*'])
            , 200);
    }

    public function updateMe(Request $request, $id) {
        $dataSelect = money_conversion::find($id);
        $dataSelect->update($request->all());

       return money_conversion::join('moneys', 'money_conversions.money_id1','=','moneys.id')
        ->join('moneys as M', 'money_conversions.money_id2','=','M.id')
        ->where('money_conversions.id','=',$id)
        ->get(['moneys.abreviation  as abreviation1',
        'moneys.money_name  as name1',
        'M.abreviation  as abreviation2',
        'M.money_name  as name2','money_conversions.*'])[0];
    }

    public function delete(Request $request, $id) {
        $selectDataDelet = money_conversion::find($id);
        if (is_null($selectDataDelet)) {
            return response()->json(['message' => 'Data not found'] ,404 );
        }
        $selectDataDelet->delete();
        return response()->json(null, 204);
    }

}
