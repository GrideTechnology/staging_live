<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Transport\RideDeliveryVehicle;
use App\Models\Common\ProviderVehicle;
use App\Models\Transport\RideType;
use App\Models\Common\CompanyCity;
use App\Models\Common\Provider;
use App\Models\Common\Menu;
use Carbon\Carbon;

class OrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($company = 1)
    {
    	Schema::disableForeignKeyConstraints();

        $store_type = DB::connection('order')->table('store_types')->where('company_id', $company)->first();

        $providers = Provider::where('company_id', $company)->get();

        foreach ($providers as $provider) {

            $provider_vehicle = ProviderVehicle::where('provider_id', $provider->id)->first();

            DB::table('provider_services')->insert([
                [
                    'provider_id' => $provider->id,
                    'company_id' => $company,
                    'admin_service' => 'ORDER',
                    'provider_vehicle_id' => ($provider_vehicle != null) ? $provider_vehicle->id : null,
                    'ride_delivery_id' => null,
                    'category_id' => $store_type->id,
                    'status' => 'ACTIVE',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            ]);

        }

	    Schema::enableForeignKeyConstraints();
    }
}
