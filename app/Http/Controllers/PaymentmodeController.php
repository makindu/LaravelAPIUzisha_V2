<?php

namespace App\Http\Controllers;

use App\Models\paymentmode;
use App\Http\Requests\StorepaymentmodeRequest;
use App\Http\Requests\UpdatepaymentmodeRequest;

class PaymentmodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise_id)
    {
        $list=collect(paymentmode::where('enterprise_id','=',$enterprise_id)->get());
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
     * @param  \App\Http\Requests\StorepaymentmodeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorepaymentmodeRequest $request)
    {
        $data=paymentmode::create($request->all());
        return $this->show($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\paymentmode  $paymentmode
     * @return \Illuminate\Http\Response
     */
    public function show(paymentmode $paymentmode)
    {
        return paymentmode::leftjoin('users as U', 'paymentmodes.user_id','=','U.id')
        ->where('paymentmodes.id', '=', $paymentmode->id)
        ->get(['U.user_name','paymentmodes.*'])[0];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\paymentmode  $paymentmode
     * @return \Illuminate\Http\Response
     */
    public function edit(paymentmode $paymentmode)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatepaymentmodeRequest  $request
     * @param  \App\Models\paymentmode  $paymentmode
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatepaymentmodeRequest $request, paymentmode $paymentmode)
    {
        $data=paymentmode::find($paymentmode->id);
        $data->update($request->all());

        return $this->show($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\paymentmode  $paymentmode
     * @return \Illuminate\Http\Response
     */
    public function destroy(paymentmode $paymentmode)
    {
        $data=paymentmode::find($paymentmode->id);
       
        return $data->delete();
    }
}
