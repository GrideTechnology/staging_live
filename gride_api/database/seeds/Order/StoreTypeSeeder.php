
<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Transport\RideDeliveryVehicle;
use App\Models\Common\ProviderVehicle;
use App\Models\Common\CompanyCity;
use App\Models\Common\Provider;
use App\Models\Common\Menu;
use Carbon\Carbon;

class StoreTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function random_color_part() {
        return str_pad( dechex( mt_rand( 0, 255 ) ), 2,'0', STR_PAD_LEFT);
    }

    public function run($company = 1)
    {
    	Schema::connection('order')->disableForeignKeyConstraints();

        DB::connection('order')->table('store_types')->insert([
            ['company_id' => $company,'name' =>'Foodie','category' =>'FOOD','status' =>'1'],
            ['company_id' => $company,'name' =>'Grocery','category' =>'OTHERS','status' =>'1'],
            ['company_id' => $company,'name' =>'Alcohol','category' =>'OTHERS','status' =>'1'],
            ['company_id' => $company,'name' =>'Flower','category' =>'OTHERS','status' =>'1']
        ]);


        $store_types = DB::connection('order')->table('store_types')->where('company_id', $company)->get();

        $menus = [];

        foreach ($store_types  as $store_type) {
            $menus[] = [
               'bg_color' =>'#'.$this->random_color_part() . $this->random_color_part() . $this->random_color_part(),
               'icon' => url('/').'/images/menus/'. strtolower( str_replace('','_', $store_type->name) ).'.png',
               'title' => $store_type->name,
               'admin_service' =>'ORDER',
               'menu_type_id' => $store_type->id,
               'company_id' => $company,
               'sort_order' => 2
            ];
        }

        DB::table('menus')->insert($menus);

        $company_cities = CompanyCity::where('company_id', $company)->get();

        $menu_city_data = [];
        $store_city_prices = [];
        $store_cities = [];

        $menu_list = DB::table('menus')->where('company_id', $company)->where('admin_service','ORDER')->get();


        foreach ($company_cities as $company_city) {

            foreach ($menu_list  as $menu) {
                $menu_city_data[] = [
                   'menu_id' => $menu->id,
                   'country_id' => $company_city->country_id,           
                   'state_id' => $company_city->state_id,             
                   'city_id' => $company_city->city_id,
                   'status' =>'1'
                ];
            }

            $store_cities[] = [
               'admin_service' =>'ORDER',
               'country_id' => $company_city->country_id,           
               'city_id' => $company_city->city_id,
               'company_id' => $company,
               'status' => 1,
            ];

            $store_city_prices[] = [
               'admin_service' =>'ORDER',
               'store_type_id' => $company,
               'country_id' =>'50',            
               'city_id' => $company_city->city_id, 
               'company_id' => $company,
               'delivery_charge' =>'50',
               'status' =>'1'
            ];
        }
        

        if(count($menu_city_data) > 0) {
            foreach (array_chunk($menu_city_data,1000) as $menu_city_datum) {
                DB::table('menu_cities')->insert($menu_city_datum);
            }
        }

        

        if(count($store_cities) > 0) {
            foreach (array_chunk($store_cities,1000) as $store_city) {
                DB::connection('order')->table('store_cities')->insert($store_city);
            }
        }

        if(count($store_city_prices) > 0) {
            foreach (array_chunk($store_city_prices,1000) as $store_city_price) {
                DB::connection('order')->table('store_city_prices')->insert($store_city_price);
            }
        }

	    Schema::connection('order')->enableForeignKeyConstraints();
    }
}
