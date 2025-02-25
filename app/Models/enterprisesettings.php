<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class enterprisesettings extends Model
{
    use HasFactory;
    protected $fillable=[
        'limit_storage',
        'storage',
        'limit_users',
        'nbr_users',
        'limit_deposits',
        'nbr_deposits',
        'limit_invoices',
        'nbr_invoices',
        'limit_services',
        'nbr_services',
        'limit_customers',
        'nbr_customers',
        'limit_funds',
        'nbr_funds',
        'limit_expenditures',
        'nbr_expenditures',
        'limit_accounts',
        'nbr_accounts',
        'limit_providers',
        'nbr_providers',
        'licence_type',
        'licence_from',
        'licence_to',
        'limit_sms',
        'nbr_sms',
        'whatsapp_activation',
        'whatsapp_api',
        'whatsapp_token',
        'limit_pos',
        'nbr_pos',
        'language',
        'enterprise_id'
    ];
}
