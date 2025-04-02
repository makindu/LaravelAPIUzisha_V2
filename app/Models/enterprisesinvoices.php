<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class enterprisesinvoices extends Model
{
    use HasFactory;

    protected $fillable = [
            'enterprise_id', 
            'plan_id', 
            'amount', 
            'currency',
            'invoice_date', 
            'due_date', 
            'status', 
            'payment_method', 
            'details',
            'description',
            'uuid'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'details' => 'array',
    ];

    
    public function enterprise()
    {
        return $this->belongsTo(Enterprises::class);
    }

    // Relation avec le plan (optionnel, car une facture peut être sans plan spécifique)
    public function plan()
    {
        return $this->belongsTo(plans::class);
    }
}
