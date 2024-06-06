<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vehicules extends Model
{
    use HasFactory;
    protected $fillable=[
        'name',
        'description ',	
        'customer_id',		
        'numero_immatriculation',	
        'annee_fabrication',		
        'date_mise_en_circulation',		
        'genre',
        'marque',	
        'type_ou_modele	',	
        'puissance',
        'numero_dans_la_serie',	
        'energie',
        'kilometrage',			
        'usage_vehicule',
        'couleur',
        'numero_chassis',
        'numero_moteur',		
        'created_by_id',
        'updated_by',
        'uuid',
        'enterprise_id'
    ];
}
