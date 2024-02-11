<?php

namespace App\Http\Controllers;

use App\Models\Fences;
use App\Http\Requests\StoreFencesRequest;
use App\Http\Requests\UpdateFencesRequest;
use App\Models\Cautions;
use App\Models\DebtPayments;
use App\Models\Expenditures;
use App\Models\FenceTicketing;
use App\Models\Invoices;
use App\Models\OtherEntries;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use stdClass;

class FencesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise)
    {
        $list=collect(Fences::where('enterprise_id','=',$enterprise)->get());
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $listdata;
    }

    /**
     * getting data for fencing
     */
    public function dataforfencing(Request $request){
        $message='';
        $fence= new stdClass;
        if(isset($request->date_concerned) && isset($request->user_id) && !empty($request->date_concerned) && !empty($request->user_id)){
            //test if already fenced?
            $ifexists=Fences::where('user_id','=',$request->user_id)->where('date_concerned','=',$request->date_concerned)->get();
            if(count($ifexists)>0){
                $message="already_fenced";
                return ['message'=>$message,'fence'=>$fence];
            }else{
                $sells=Invoices::whereBetween('created_at',[$request->date_concerned.' 00:00:00',$request->date_concerned.' 23:59:59'])
                // ->where('type_facture','=','cash')->orWhere('type_facture','=','credit')
                ->where('edited_by_id','=',$request->user_id)->get();

                $entries=OtherEntries::whereBetween('created_at',[$request->date_concerned.' 00:00:00',$request->date_concerned.' 23:59:59'])
                ->where('user_id','=',$request->user_id)->get();

                $payments=DebtPayments::whereBetween('created_at',[$request->date_concerned.' 00:00:00',$request->date_concerned.' 23:59:59'])
                ->where('done_by_id','=',$request->user_id)->get();

                $expenditures=Expenditures::whereBetween('created_at',[$request->date_concerned.' 00:00:00',$request->date_concerned.' 23:59:59'])
                ->where('user_id','=',$request->user_id)->get();

                $cautions=Cautions::whereBetween('created_at',[$request->date_concerned.' 00:00:00',$request->date_concerned.' 23:59:59'])
                ->where('user_id','=',$request->user_id)->get();

                $objet =['sells'=>$sells,'payments'=>$payments,'expenditures'=>$expenditures,'cautions'=>$cautions,'entries'=>$entries];
                return ['message'=>$message,'fence'=>$objet];
            }
        }
        else if(isset($request->user_id) && !empty($request->user_id) && empty($request->date_concerned)){
                $date_concerned=date('Y-m-d');
              //test if already fenced?
              $ifexists=Fences::where('user_id','=',$request->user_id)->where('date_concerned','=',$date_concerned)->get();
              if(count($ifexists)>0){
                  $message="already_fenced";
                  return ['message'=>$message,'fence'=>$fence];
              }else{
                  $sells=Invoices::whereBetween('created_at',[$date_concerned.' 00:00:00',$date_concerned.' 23:59:59'])
                  // ->where('type_facture','=','cash')->orWhere('type_facture','=','credit')
                  ->where('edited_by_id','=',$request->user_id)->get();

                  $entries=OtherEntries::whereBetween('created_at',[$date_concerned.' 00:00:00',$date_concerned.' 23:59:59'])
                  ->where('user_id','=',$request->user_id)->get();
  
                  $payments=DebtPayments::whereBetween('created_at',[$date_concerned.' 00:00:00',$date_concerned.' 23:59:59'])
                  ->where('done_by_id','=',$request->user_id)->get();
  
                  $expenditures=Expenditures::whereBetween('created_at',[$date_concerned.' 00:00:00',$date_concerned.' 23:59:59'])
                  ->where('user_id','=',$request->user_id)->get();
  
                  $cautions=Cautions::whereBetween('created_at',[$date_concerned.' 00:00:00',$date_concerned.' 23:59:59'])
                  ->where('user_id','=',$request->user_id)->get();
  
                  $objet =['sells'=>$sells,'payments'=>$payments,'expenditures'=>$expenditures,'cautions'=>$cautions,'entries'=>$entries];
                  return ['message'=>$message,'fence'=>$objet];
              }
        }
        else{
            $message="data_no_conform";
            return ['message'=>$message,'fence'=>$fence];
        }
    }   
    
    /**
     * getting data for fencing
     */
    public function dataforfencingsumerized(Request $request){
        $message='';
        $fence= new stdClass;
        if(isset($request->date_concerned) && isset($request->user_id) && !empty($request->date_concerned) && !empty($request->user_id)){
            //test if already fenced?
            $ifexists=Fences::where('user_id','=',$request->user_id)->where('date_concerned','=',$request->date_concerned)->get();
            if(count($ifexists)>0){
                $message="already_fenced";
                return ['message'=>$message,'fence'=>$fence];
            }else{
                $sellsCash=Invoices::whereBetween('created_at',[$request->date_concerned.' 00:00:00',$request->date_concerned.' 23:59:59'])
                ->where('type_facture','=','cash')
                ->where('edited_by_id','=',$request->user_id)->get();
                
                $sellsCredit=Invoices::whereBetween('created_at',[$request->date_concerned.' 00:00:00',$request->date_concerned.' 23:59:59'])
                ->where('type_facture','=','credit')
                ->where('edited_by_id','=',$request->user_id)->get();

                $entries=OtherEntries::whereBetween('created_at',[$request->date_concerned.' 00:00:00',$request->date_concerned.' 23:59:59'])
                ->where('user_id','=',$request->user_id)->get();

                $payments=DebtPayments::whereBetween('created_at',[$request->date_concerned.' 00:00:00',$request->date_concerned.' 23:59:59'])
                ->where('done_by_id','=',$request->user_id)->get();

                $expenditures=Expenditures::whereBetween('created_at',[$request->date_concerned.' 00:00:00',$request->date_concerned.' 23:59:59'])
                ->where('user_id','=',$request->user_id)->get();

                $cautions=Cautions::whereBetween('created_at',[$request->date_concerned.' 00:00:00',$request->date_concerned.' 23:59:59'])
                ->where('user_id','=',$request->user_id)->get();

                // $objet =['sells'=>$sells->sum('total'),'payments'=>$payments->sum('amount_payed'),'expenditures'=>$expenditures->sum('amount'),'cautions'=>$cautions->sum('amount'),'entries'=>$entries->sum('amount')];
                $objet=[ 'user_id'=>$request->user_id,
                'amount_due'=>($sellsCash->sum('total')+$cautions->sum('amount')+$payments->sum('amount_payed')+$entries->sum('amount'))-($sellsCredit->sum('total')+$expenditures->sum('amount')),
                'amount_paid'=>0,
                'money_id'=>$this->getdefaultmoney($this->getEse($request->user_id)['id'])['id'],
                'totalsell'=>$sellsCash->sum('total')+$sellsCredit->sum('total'),
                'totalcash'=>$sellsCash->sum('total'),
                'totalcredits'=>$sellsCredit->sum('total'),
                'totalbonus'=>0,
                'totalcautions'=>$cautions->sum('amount'),
                'totaldebts'=>$payments->sum('amount_payed'),
                'depositcautions'=>$cautions->sum('amount'),
                'totalexpenditures'=>$expenditures->sum('amount'),
                'totalentries'=>$entries->sum('amount'),
                'sold'=>0,
                'enterprise_id'=>$this->getEse($request->user_id)['id']];
                    // $objet =['sells'=>$sells->sum('total'),'payments'=>$payments->sum('amount_payed'),'expenditures'=>$expenditures->sum('amount'),'cautions'=>$cautions->sum('amount'),'entries'=>$entries->sum('amount')];
                    return ['message'=>$message,'fence'=>$objet];
                }
        }
        else if(isset($request->user_id) && !empty($request->user_id) && empty($request->date_concerned)){
                $date_concerned=date('Y-m-d');
              //test if already fenced?
              $ifexists=Fences::where('user_id','=',$request->user_id)->where('date_concerned','=',$date_concerned)->get();
              if(count($ifexists)>0){
                  $message="already_fenced";
                  return ['message'=>$message,'fence'=>$fence];
              }else{
                $sellsCash=Invoices::whereBetween('created_at',[$request->date_concerned.' 00:00:00',$request->date_concerned.' 23:59:59'])
                ->where('type_facture','=','cash')
                ->where('edited_by_id','=',$request->user_id)->get();
                
                $sellsCredit=Invoices::whereBetween('created_at',[$request->date_concerned.' 00:00:00',$request->date_concerned.' 23:59:59'])
                ->where('type_facture','=','credit')
                ->where('edited_by_id','=',$request->user_id)->get();

                  $entries=OtherEntries::whereBetween('created_at',[$date_concerned.' 00:00:00',$date_concerned.' 23:59:59'])
                  ->where('user_id','=',$request->user_id)->get();
  
                  $payments=DebtPayments::whereBetween('created_at',[$date_concerned.' 00:00:00',$date_concerned.' 23:59:59'])
                  ->where('done_by_id','=',$request->user_id)->get();
  
                  $expenditures=Expenditures::whereBetween('created_at',[$date_concerned.' 00:00:00',$date_concerned.' 23:59:59'])
                  ->where('user_id','=',$request->user_id)->get();
  
                  $cautions=Cautions::whereBetween('created_at',[$date_concerned.' 00:00:00',$date_concerned.' 23:59:59'])
                  ->where('user_id','=',$request->user_id)->get();
  
                  $objet=[ 'user_id'=>$request->user_id,
                  'amount_due'=>($sellsCash->sum('total')+$cautions->sum('amount')+$payments->sum('amount_payed')+$entries->sum('amount'))-($sellsCredit->sum('total')+$expenditures->sum('amount')),
                  'amount_paid'=>0,
                  'money_id'=>$this->getdefaultmoney($this->getEse($request->user_id)['id'])['id'],
                  'totalsell'=>$sellsCash->sum('total')+$sellsCredit->sum('total'),
                  'totalcash'=>$sellsCash->sum('total'),
                  'totalcredits'=>$sellsCredit->sum('total'),
                  'totalbonus'=>0,
                  'totalcautions'=>$cautions->sum('amount'),
                  'totaldebts'=>$payments->sum('amount_payed'),
                  'depositcautions'=>$cautions->sum('amount'),
                  'totalexpenditures'=>$expenditures->sum('amount'),
                  'totalentries'=>$entries->sum('amount'),
                  'sold'=>0,
                  'enterprise_id'=>$this->getEse($request->user_id)['id']];
                  return ['message'=>$message,'fence'=>$objet];
              }
        }
        else{
            $message="data_no_conform";
            return ['message'=>$message,'fence'=>$fence];
        }
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
     * new store for pressing
     */
    public function pressingNew(Request $request){
        $message="";
        if(isset($request->date_concerned) && isset($request->user_id) && !empty($request->date_concerned) && !empty($request->user_id)){
            //test if already fenced?
            $ifexists=Fences::where('user_id','=',$request->user_id)->where('date_concerned','=',$request->date_concerned)->get();
            if(count($ifexists)>0){
                $message="already_fenced";
                return $message;
            }else{
                $newdate=Carbon::create($request->date_concerned);
                $request['date_concerned']=$newdate;
                $newfence=Fences::create($request->all());
                if($request->ticketings){
                    foreach($request->ticketings as $ticketing){
                        $ticketing['fence_id']=$newfence['id'];
                        FenceTicketing::create($ticketing);
                    }
                }
                return $this->show($newfence);
            }
        }
        else{
            $message="data_no_conform";
            return $message;
        } 
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreFencesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $message="";
        if(isset($request->date_concerned) && isset($request->user_id) && !empty($request->date_concerned) && !empty($request->user_id)){
            //test if already fenced?
            $ifexists=Fences::where('user_id','=',$request->user_id)->where('date_concerned','=',$request->date_concerned)->get();
            if(count($ifexists)>0){
                $message="already_fenced";
                return $message;
            }else{
                $newdate=Carbon::create($request->date_concerned);
                $request['date_concerned']=$newdate;
                $newfence=Fences::create($request->all());
                if($request->ticketings){
                    foreach($request->ticketings as $ticketing){
                        $ticketing['fence_id']=$newfence['id'];
                        FenceTicketing::create($ticketing);
                    }
                }
                return $this->show($newfence);
            }
        }
        else{
            $message="data_no_conform";
            return $message;
        } 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Fences  $fences
     * @return \Illuminate\Http\Response
     */
    public function show(Fences $fences)
    {
        $newfence=Fences::leftjoin('moneys as M','fences.money_id','=','M.id')
        ->leftjoin('users as U','fences.user_id','=','U.id')
        ->where('fences.id','=',$fences->id)
        ->get(['M.money_name','M.abreviation','U.user_name','fences.*'])[0];

        $ticketings= FenceTicketing::leftjoin('moneys as M','fence_ticketings.money_id','=','M.id')
        ->leftjoin('fences as F','fence_ticketings.fence_id','=','F.id')
        ->where('fence_ticketings.fence_id','=',$newfence['id'])
        ->get(['M.money_name','M.abreviation','fence_ticketings.*']);

        return ['fence'=>$newfence,'ticketings'=>$ticketings];
    }

    public function getone($fenceid){
        $goten=Fences::find($fenceid);
        return $this->show($goten);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Fences  $fences
     * @return \Illuminate\Http\Response
     */
    public function edit(Fences $fences)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateFencesRequest  $request
     * @param  \App\Models\Fences  $fences
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFencesRequest $request, Fences $fences)
    {
        return $this->show(Fences::find($fences->update($request->all())));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Fences  $fences
     * @return \Illuminate\Http\Response
     */
    public function destroy(Fences $fences)
    {
        return Fences::destroy($fences);
    }

    public function delete2($id){
        $find=Fences::find($id);
        return $find->delete();
    }
}
