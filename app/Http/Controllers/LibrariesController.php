<?php

namespace App\Http\Controllers;

use App\Models\libraries;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorelibrariesRequest;
use App\Http\Requests\UpdatelibrariesRequest;
use App\Models\sharedlibraries;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class LibrariesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
          $images = Libraries::where('enterprise_id', $request['enterprise_id'])->whereIn('extension',['png','jpg','jpeg'])->get();
          $docs = Libraries::where('enterprise_id', $request['enterprise_id'])->whereNotIn('extension',['png','jpg','jpeg'])->get();
         
          $medias=$this->groupdata($images);
          $documents=$this->groupdata($docs);
            
            return response()->json([
                "status"=>200,
                "message"=>"success",
                "medias"=> $medias,
                "mediascounter"=>$images->count(),
                "documentscounter"=> $docs->count(),
                "documents"=> $documents,
                "error"=>null
            ]);
        } catch (Exception $th) {
            return response()->json([
                "status"=>500,
                "message"=>"error",
                "data"=>null,
                "error"=>$th->getMessage()
            ]);
        }
       
    }

    /**
     * get file by name
     */
    public function getfile($filename){
        return json_encode(["path"=>$filename]);
        // $path = storage_path("app/public/uploads/{$filename}");
        // return json_encode(["path"=>$path]);
    
        // if (!file_exists($path)) {
        //     return response()->json(['error' => 'File not found'], 404);
        // }
    
        // return Response::file($path);
    }

    public function getfilebyname(Request $request){
         $path = storage_path("app/public/uploads/{$request['filename']}");
        // return json_encode(["path"=>$path]);
    
        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return Response::file($path);
        // return json_encode(["path"=>$path]);
    }
    /**
     * Delete multiple data
     */
    public function deletemultiple(Request $request){
        $notdeleted=[];
        $deleted=[];
        try {
            if ($request['user_id'] && $request['enterprise_id']) {
                $affectation=$this->userenterpriseaffectation($request['user_id'],$request['enterprise_id']);
                if ($affectation) {
                    if ($request['data'] && count($request['data'])>0) {
                        foreach ($request['data'] as $key => $library) {
                            $libraryfind=libraries::find($library['id']);
                           
                            if ($libraryfind && $library['user_id']==$request['user_id']) {
                                if (Storage::disk('public')->exists($libraryfind['path'])) {
                                   Storage::disk('public')->delete($libraryfind['path']);
                                }
                                $libraryfind->delete();
                                array_push($deleted,$library);
                            }else{
                                array_push($notdeleted,$library);
                            }
                        }
                        return response()->json([
                            "status"=>200,
                            "message"=>"success",
                            "error"=>null,
                            "deleted"=>$deleted,
                            "notdeleted"=>$notdeleted
                        ]);
                    }else{
                        return response()->json([
                            "status"=>400,
                            "message"=>"error",
                            "data"=>null,
                            "error"=>'no data sent'
                        ]);
                    }
                }else{
                    return response()->json([
                        "status"=>400,
                        "message"=>"error",
                        "data"=>null,
                        "error"=>'user not affected'
                    ]);
                }
            }else{
                return response()->json([
                    "status"=>400,
                    "message"=>"error",
                    "data"=>null,
                    "error"=>'incorrects informations about owner'
                ]);   
            }
        } catch (Exception $th) {
            return response()->json([
                "status"=>500,
                "message"=>"error",
                "data"=>null,
                "error"=>$th->getMessage()
            ]);
        }
    }

    public function getstorage($enterpriseId){
        try {
            $storage=json_decode($this->enterpriseSettings($enterpriseId));
           
            return response()->json([
                "status"=>200,
                "message"=>"success",
                "storage"=>$storage->storage_allocated,
                "mediasize"=>$storage->medias_used,
                "documentsize"=>$storage->docs_used,
                "totalused"=>$storage->total_used,
                "remainingStorage"=>$storage->remaining,
                "error"=>null
            ]);
        } catch (Exception $th) {
            return response()->json([
                "status"=>500,
                "message"=>"error",
                "data"=>null,
                "error"=>$th->getMessage()
            ]);
        }
      
    }

    private function groupdata($collection){
            $grouped =$collection->groupBy(function ($item) { 
            return Carbon::parse($item->created_at)->format('Y-m'); });
            $currentMonth = Carbon::now()->format('Y-m'); 
            $monthsInFrench = [ '01' => 'Janvier', '02' => 'Février','03' => 'Mars', '04' => 'Avril', '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août', '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre', ];
            $sorted=$grouped->sortBy(function($value,$key) use($currentMonth){
                return $key==$currentMonth?-1:1;
            });
            $finalData = []; 
            foreach ($sorted as $month => $librariesInMonth){
                $monthParts = explode('-', $month);
                $monthName = $monthsInFrench[$monthParts[1]] . '-' . $monthParts[0]; 
                if ($month == $currentMonth){$monthName = "Ce mois-ci"; }
                  $finalData[] = [ 
                        'month' => $monthName,
                        'libraries' => $librariesInMonth->toArray()  
                    ];
            }
        return $finalData; 
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
                'attachments.*'=>'required|mimes:png,jpg,jpeg,pdf,doc,docx,xlsx,xls,csv',
                'descriptions.*'=>'nullable|string',
                'user_id'=>'required|integer',
                'enterprise_id'=>'required|integer'
            ]);
            $userId=$request->input('user_id');
            $enterpriseId=$request->input('enterprise_id');
            $uploadedFiles=$request->file('attachments');
            $descriptions=$request->input('descriptions',[]);
            $filesPaths=[];
            $totalsize=0;
            $remainingstorage=$this->reamingstorage($enterpriseId);
            foreach ($uploadedFiles as $index=> $file) {
                $totalsize +=$file->getSize();
            }

            if (($totalsize/1024000)>$remainingstorage) {
                return response()->json([
                    "status"=>400,
                    "message"=>"error",
                    "error"=>"no longer enough space",
                    "data"=>null
                ]);
            }

            foreach ($uploadedFiles as $index=> $file) {
               $path=$file->store('uploads','public');
                $description=isset($descriptions[$index])?$descriptions[$index]:'';
                $newadded=libraries::create([
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
                array_push($filesPaths,$newadded);
            }
            
            $images =collect($filesPaths)->whereIn('extension',['png','jpg','jpeg']);
            $docs =collect($filesPaths)->whereNotIn('extension',['png','jpg','jpeg']);
            return response()->json([
                "status"=>200,
                "message"=>"success",
                "error"=>null,
                "medias"=>$images,
                "documents"=>$docs
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
