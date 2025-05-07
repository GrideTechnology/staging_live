<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class ProviderAgreeDocument extends BaseModel
{
	protected $connection = 'common';
    
    protected $table = 'provider_agree_document';

    protected $fillable = [
     	'user_id', 'document_id', 'status' 
     ];
}
