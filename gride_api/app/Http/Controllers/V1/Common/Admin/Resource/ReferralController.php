<?php
namespace App\Http\Controllers\V1\Common\Admin\Resource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Helpers\Helper;
use App\Models\Common\User;
use App\Traits\Encryptable;

use Auth;
use DB;
class ReferralController extends Controller{
    use Actions;
    use Encryptable;

    private $model;
    private $request;
 public function index($id)
    {
        try {
            $user = User::find($id);
            // $user['city_data'] = CompanyCity::where("country_id", $user['country_id'])->with('city')->get();
            return Helper::getResponse(['data' => $user]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'error' => $e->getMessage()]);
        }
    }
}