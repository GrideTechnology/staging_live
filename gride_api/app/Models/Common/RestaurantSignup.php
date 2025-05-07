<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class RestaurantSignup extends BaseModel
{
	protected $connection = 'common';
    
    protected $table = 'restaurant_user_signup';

    protected $fillable = [
     	'first_name_new', 'last_name_new', 'email_new', 'phone_new', 'phone_code_new', 'service_radio', 'size','restro_radio','restro_name','restro_phone','restro_company_name','cars_type','tax_company_name','tax_FEIN','payment_firstname','payment_lastname','payment_bankname','acc_number','payment_ach_number','created_at', 'updated_at', 
     ];
}
