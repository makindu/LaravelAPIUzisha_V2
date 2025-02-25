<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class images extends Model
{
    use HasFactory;
    protected $fillable=[
        'doc_link',
        'description',
        'type_operation',
        'status',
        'ref_operation',
        'done_by',
        'enterprise_id',
        'size',
        'principal',
    ];
}
