<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enterprises extends Model
{
    use HasFactory;
    protected $fillable=[
        'name',
        'description',
        'rccm',
        'national_identification',
        'num_impot',
        'autorisation_fct',
        'adresse',
        'phone',
        'mail',
        'website',
        "facebook",
        "instagram",
        "linkdin",
        'logo',
        'category',
        'vat_rate',
        'uuid',
        'sync_status',
        'user_id',
        'status',
        'invoicefooter',
        'fidelitypointvalue',
        'fidelitydefaultmode',
        'initvaluefidelity',
        'date_from_fidelity',
        'date_to_fidelity'
    ];

    public function invoices()
    {
        return $this->hasMany(enterprisesinvoices::class);
    }
}