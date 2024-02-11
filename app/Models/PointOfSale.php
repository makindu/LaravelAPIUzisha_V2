<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointOfSale extends Model
{
    use HasFactory;
    protected $fillable=[
       'user_id',
       'name',
       'description',
       'type',
       'sold',
       'nb_sales_bonus',
       'bonus_percentage',
       'workforce_percent',
       'rccm',	
       'national_identification',
       'num_impot',	
       'autorisation_fct',
       'adresse',	
       'phone',
       'mail',
       'website',	
       'logo',	
       'category',	   
       'vat_rate',	
       'uuid',	
       'status',	
       'enterprise_id'
    ];
}
