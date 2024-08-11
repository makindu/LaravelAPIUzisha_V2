<?php

namespace App\Http\Controllers;

use App\Models\posusers;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreposusersRequest;
use App\Http\Requests\UpdateposusersRequest;
use App\Models\User;
use Illuminate\Http\Request;

class PosusersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($posid)
    {
        $list=collect(posusers::where('pos_id','=',$posid)->get());
        $list=$list->map(function ($pos){
                $user=User::find($pos['user_id']);
                return $user;
        });

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
     * @param  \App\Http\Requests\StoreposusersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreposusersRequest $request)
    {
        //if it exists
        $ifexists=posusers::where('user_id','=',$request['user_id'])->first();
        if (!$ifexists) {
            $new=posusers::create($request->all());
            return response()->json([
                'message'=>"success",
                'data'=>User::find($new['user_id'])
            ]) ;
        } else {
            return response()->json([
                'message'=>"already affected",
                'data'=>null
            ]) ;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\posusers  $posusers
     * @return \Illuminate\Http\Response
     */
    public function show(posusers $posusers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\posusers  $posusers
     * @return \Illuminate\Http\Response
     */
    public function edit(posusers $posusers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateposusersRequest  $request
     * @param  \App\Models\posusers  $posusers
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateposusersRequest $request, posusers $posusers)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\posusers  $posusers
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $data=posusers::where('user_id','=',$request['user_id'])->where('pos_id','=',$request['pos_id'])->first();
        if ($data) {
            $data->delete();
           $message=$data;
        }else{
            $message="failed";
        }

        return $message;
    }
}
