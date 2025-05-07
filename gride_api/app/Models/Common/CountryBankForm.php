<?php

namespace App\Models\Common;
use App\Models\BaseModel;
use Auth;


class CountryBankForm extends BaseModel
{
    protected $connection = 'common';
	
	protected $hidden = [
     	'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];

    public function country()
    {
        return $this->belongsTo('App\Models\Common\Country', 'country_id', 'id');
    }

       public function bankdetails()
    {
     
    return $this->hasone('App\Models\Common\ProviderBankdetail', 'bankform_id','id');
    }
}
