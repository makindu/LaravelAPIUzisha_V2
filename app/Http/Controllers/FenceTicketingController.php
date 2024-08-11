<?php

namespace App\Http\Controllers;

use App\Models\FenceTicketing;
use App\Http\Requests\StoreFenceTicketingRequest;
use App\Http\Requests\UpdateFenceTicketingRequest;
use Illuminate\Support\Facades\DB;
use stdClass;

class FenceTicketingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list=collect(FenceTicketing::all());
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
     * @param  \App\Http\Requests\StoreFenceTicketingRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFenceTicketingRequest $request)
    {
        $new = fenceTicketing::create($request->all());
        if ($new) {
           //getting the sum of all tickets
           $sum=FenceTicketing::join('fences as F','fence_ticketings.fence_id','=','F.id')
           ->select(DB::raw('SUM(amount) as total'))
           ->where('fence_id','=',$request['fence_id'])
           ->get('total','amount_due')->first();

           DB::update('update fences set amount_paid= ? sold = ? where id = ? ',[$sum['total'],$sum['amount_due']-$sum['total'],$request->fence_id]);
           return $this->show($new);

        }else{
            return new stdClass;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FenceTicketing  $fenceTicketing
     * @return \Illuminate\Http\Response
     */
    public function show(FenceTicketing $fenceTicketing)
    {
       return FenceTicketing::leftjoin('moneys as M','fence_ticketings.money_id','=','M.id')
        ->leftjoin('fences as F','fence_ticketings.fence_id','=','F.id')
        ->leftjoin('users as U','F.user_id','=','U.id')
        ->where('fence_ticketings.id','=',$fenceTicketing->id)
        ->get(['U.user_name','M.money_name','M.abreviation','fence_ticketings.*'])[0];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FenceTicketing  $fenceTicketing
     * @return \Illuminate\Http\Response
     */
    public function edit(FenceTicketing $fenceTicketing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateFenceTicketingRequest  $request
     * @param  \App\Models\FenceTicketing  $fenceTicketing
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFenceTicketingRequest $request, FenceTicketing $fenceTicketing)
    {
       return $this->show(fenceTicketing::find($fenceTicketing->update($request->all())));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FenceTicketing  $fenceTicketing
     * @return \Illuminate\Http\Response
     */
    public function destroy(FenceTicketing $fenceTicketing)
    {
        return FenceTicketing::destroy($fenceTicketing);
    }
}
