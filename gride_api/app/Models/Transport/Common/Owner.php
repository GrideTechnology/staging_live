<?php 

namespace App\Models\Common;

use App\Models\BaseModel;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use App\Traits\Encryptable;

class Owner extends BaseModel implements JWTSubject, AuthenticatableContract, AuthorizableContract
{
    protected $connection = 'common';

    use Authenticatable, Authorizable;

    use Encryptable;

    protected $encryptable = [
        'email',
        'phone'       
    ];

     

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'gender', 'email', 'phone', 'picture', 'password', 'address', 'company_id', 'device_type','jwt_token','login_by', 'device_token', 'is_deleted', 'deleted_at'
    ];
 
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
     'password', 'jwt_token', 'device_id', 'device_type', 'device_token', 'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by','updated_at',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
   

    public function scopeSearch($query, $searchText='') {
        return $query
            ->where('name', 'like', "%" . $searchText . "%")
            ->orWhere('email', 'like', "%" .$this->cusencrypt($searchText,env('DB_SECRET')). "%")
            ->orWhere('phone', 'like', "%" . $this->cusencrypt($searchText,env('DB_SECRET')) . "%")
            ->orWhere('address', 'like', "%" . $searchText . "%");
    }
}

