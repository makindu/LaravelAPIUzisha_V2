<?php

namespace App\Http\Controllers;

use App\Models\DetailsInvoicesStatus;
use App\Http\Requests\StoreDetailsInvoicesStatusRequest;
use App\Http\Requests\UpdateDetailsInvoicesStatusRequest;
use Illuminate\Http\Request;
use stdClass;

class DetailsInvoicesStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoreDetailsInvoicesStatusRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDetailsInvoicesStatusRequest $request)
    {
        $response=new stdClass;
        //check if it has an old status
        $old=DetailsInvoicesStatus::where('detail_id','=',$request['detail_id'])->get();
        if (count($old)>0) {
            //update the latest
            $latest=DetailsInvoicesStatus::where('detail_id','=',$request['detail_id'])->get()->last();
            if ($latest) {
                $request_update= new Request([
                    'detail_id'=>$latest['detail_id'],
                    'status_id'=>$latest['status_id'],
                    'from'=>$latest['from'],
                    'enterprise_id'=>$latest['enterprise_id'],
                    'to'=>date('Y-m-d'),
                    'user_id'=>$latest['user_id']
                ]);

                $updated=$latest->update($request_update->all());

                if ($updated) {
                    $request['from']=date('Y-m-d');
                    $request['to']=null;
                    $ese=$this->getEse($request['user_id']);
                    $request['enterprise_id']=$ese['id'];
                    $response=$this->show(DetailsInvoicesStatus::create($request->all()));
                }
            }
        } else {
            //new collection
            $request['from']=date('Y-m-d');
            $ese=$this->getEse($request['user_id']);
            $request['enterprise_id']=$ese['id'];
            $response=$this->show(DetailsInvoicesStatus::create($request->all()));
        }

        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DetailsInvoicesStatus  $detailsInvoicesStatus
     * @return \Illuminate\Http\Response
     */
    public function show(DetailsInvoicesStatus $detailsInvoicesStatus)
    {
        return DetailsInvoicesStatus::join('statuses as ST','details_invoices_statuses.status_id','=','ST.id')->where('details_invoices_statuses.id','=',$detailsInvoicesStatus['id'])->get(['details_invoices_statuses.*','ST.name'])->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DetailsInvoicesStatus  $detailsInvoicesStatus
     * @return \Illuminate\Http\Response
     */
    public function edit(DetailsInvoicesStatus $detailsInvoicesStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDetailsInvoicesStatusRequest  $request
     * @param  \App\Models\DetailsInvoicesStatus  $detailsInvoicesStatus
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDetailsInvoicesStatusRequest $request, DetailsInvoicesStatus $detailsInvoicesStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DetailsInvoicesStatus  $detailsInvoicesStatus
     * @return \Illuminate\Http\Response
     */
    public function destroy(DetailsInvoicesStatus $detailsInvoicesStatus)
    {
        //
    }
}
