<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'=>(string) $this->id,
            'department_name'=>$this->department_name,
            'createdby'=>$this->createdby,
            'description'=>$this->description,
            'header_depart'=>$this->header_depart,
            'requests'=>$this->requests,
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at
        ];

    }
}
