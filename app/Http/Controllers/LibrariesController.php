<?php

namespace App\Http\Controllers;

use App\Models\libraries;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorelibrariesRequest;
use App\Http\Requests\UpdatelibrariesRequest;
use Exception;
use Illuminate\Http\Request;

class LibrariesController extends Controller
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
     * @param  \App\Http\Requests\StorelibrariesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        try {
            $request->validate([
                'attachments.*'=>'required|mimes:png,jpg,pdf,docx,xlsx,xls,csv|max:3000',
                'descriptions.*'=>'nullable|string',
                'user_id'=>'required|integer',
                'enterprise_id'=>'required|integer'
            ]);
            $userId=$request->input('user_id');
            $enterpriseId=$request->input('enterprise_id');
            $uploadedFiles=$request->file('attachments');
            $descriptions=$request->input('descriptions',[]);
            $filesPaths=[];
            foreach ($uploadedFiles as $index=> $file) {
               $path=$file->store('uploads','public');
                array_push($filesPaths,$path);
                $description=isset($descriptions[$index])?$descriptions[$index]:'';
                libraries::create([
                    'name'=>pathinfo($file->getClientOriginalName(),PATHINFO_FILENAME) ,
                    'description'=>$description,
                    'done_at'=>date('Y-m-d'),
                    'size'=>$file->getSize(),
                    'uuid'=>$this->getUuId('C','A').$index,
                    'type'=>$file->getClientMimeType(),
                    'path'=> $path,
                    'enterprise_id'=>$enterpriseId,
                    'user_id'=>$userId,
                    'extension'=>$file->getClientOriginalExtension()
                ]);
            }

            return response()->json([
                "status"=>200,
                "message"=>"success",
                "error"=>null,
                "data"=>$filesPaths
            ]);
        } catch (Exception $th) {
            return response()->json([
                "status"=>500,
                "message"=>"error",
                "error"=>$th->getMessage(),
                "data"=>null
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\libraries  $libraries
     * @return \Illuminate\Http\Response
     */
    public function show(libraries $libraries)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\libraries  $libraries
     * @return \Illuminate\Http\Response
     */
    public function edit(libraries $libraries)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatelibrariesRequest  $request
     * @param  \App\Models\libraries  $libraries
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatelibrariesRequest $request, libraries $libraries)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\libraries  $libraries
     * @return \Illuminate\Http\Response
     */
    public function destroy(libraries $libraries)
    {
        //
    }
}
