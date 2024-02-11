<?php

namespace App\Http\Controllers;

use App\Models\request_references;
use App\Http\Requests\Storerequest_referencesRequest;
use App\Http\Requests\Updaterequest_referencesRequest;
use Illuminate\Http\Request;

class RequestReferencesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return request_references::all();
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
        return request_references::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\request_references  $request_references
     * @return \Illuminate\Http\Response
     */
    public function show(request_references $request_references)
    {
        return request_references::find($request_references);
    }

    public function getreferencesbyrequest($id){

        $data =request_references::
        where('request_id', '=', $id)
        ->get();
        return response()->json($data);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\request_references  $request_references
     * @return \Illuminate\Http\Response
     */
    public function edit(request_references $request_references)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Updaterequest_referencesRequest  $request
     * @param  \App\Models\request_references  $request_references
     * @return \Illuminate\Http\Response
     */
    public function update(Updaterequest_referencesRequest $request, request_references $request_references)
    {
        $element = request_references::find($request_references);
        return $element->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\request_references  $request_references
     * @return \Illuminate\Http\Response
     */
    public function destroy(request_references $request_references)
    {
        return request_references::destroy($request_references);
    }

}
