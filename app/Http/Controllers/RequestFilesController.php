<?php

namespace App\Http\Controllers;

use App\Models\request_files;
use App\Http\Requests\Storerequest_filesRequest;
use App\Http\Requests\Updaterequest_filesRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestFilesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return request_files::all();
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
       return request_files::create($request->all());
    }

    public function getsfiles(){

        $target_path ="../uploads";
        $target_path = $target_path.basename($_FILES['file']['name']);

        if(move_uploaded_file($_FILES['file']['tmp_name'],$target_path)){
            $data = ['success'=>true,'message'=>'uploaded'];
            return $data;
        }else{
            $data = ['success'=>false,'message'=>'not uploaded'];
            return $data; 
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\request_files  $request_files
     * @return \Illuminate\Http\Response
     */
    public function show(request_files $request_files)
    {
        return request_files::find($request_files);
    }

    public function getfilesbyrequest($id){

        $data =request_files::
        where('request_id', '=', $id)
        ->get();
        return response()->json($data);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\request_files  $request_files
     * @return \Illuminate\Http\Response
     */
    public function edit(request_files $request_files)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Updaterequest_filesRequest  $request
     * @param  \App\Models\request_files  $request_files
     * @return \Illuminate\Http\Response
     */
    public function update(Updaterequest_filesRequest $request, request_files $request_files)
    {
        $element = request_files::find($request_files);
        return $element->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\request_files  $request_files
     * @return \Illuminate\Http\Response
     */
    public function destroy(request_files $request_files)
    {
        return request_files::destroy($request_files);
    }

}
