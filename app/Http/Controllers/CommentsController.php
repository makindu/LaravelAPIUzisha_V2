<?php

namespace App\Http\Controllers;

use App\Models\comments;
use App\Http\Requests\StorecommentsRequest;
use App\Http\Requests\UpdatecommentsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return comments::all();
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
        $response =  comments::create($request->all());
        $id = $response->id;
        $data = DB::table('comments as C')
        ->where('C.id','=',$id)
        ->join('users as U', 'C.user_id','=','U.id')
        ->get(['C.id as id','C.comment','C.created_at as date_comment','C.request_id', 'C.user_id', 'U.user_name as sentbyname']);
        return response()->json($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\comments  $comments
     * @return \Illuminate\Http\Response
     */
    public function show(comments $comments)
    {
        return comments::find($comments);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\comments  $comments
     * @return \Illuminate\Http\Response
     */
    public function edit(comments $comments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatecommentsRequest  $request
     * @param  \App\Models\comments  $comments
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatecommentsRequest $request, comments $comments)
    {
        $element = comments::find($comments);
        return $element->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\comments  $comments
     * @return \Illuminate\Http\Response
     */
    public function destroy(comments $comments)
    {
        return comments::destroy($comments);
    }


    public function getCommentByIdRequest($id) {
        // $this->C.created_at->format("Y-m-d");
        $data = DB::table('comments as C')
        ->where('C.request_id', '=', $id)
        ->join('users as U', 'C.user_id','=','U.id')
        ->get(['C.id as id','C.comment','C.created_at as date_comment','C.request_id', 'C.user_id', 'U.user_name as sentbyname']);
        return response()->json($data);
    }

}
