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

class TransportTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($company = 1)
    {
    	Schema::disableForeignKeyConstraints();

        $taxi_ride = RideType::create([
            'company_id' => $company,
            'ride_name' => 'Taxi Ride',
            'status' => '1'
        ]);

        $moto_ride = RideType::create([
            'company_id' => $company,
            'ride_name' => 'Moto Ride',
            'status' => '1'
        ]);

    	

        $ride_delivery_vehicles = [
            ['name' => 'Sedan', 'vehicle_image' => url('/').'/images/transport/sedan.png', 'vehicle_marker' => url('/').'/images/transport/sedan_marker.png'],
            ['name' => 'Premium', 'vehicle_image' => url('/').'/images/transport/premium.png', 'vehicle_marker' => url('/').'/images/transport/grey-car_marker.png'],
            ['name' => 'Hatchback', 'vehicle_image' => url('/').'/images/transport/hatchback.png', 'vehicle_marker' => url('/').'/images/transport/grey-car_marker.png'],
            ['name' => 'Minivan', 'vehicle_image' => url('/').'/images/transport/business.png', 'vehicle_marker' => url('/').'/images/transport/grey-car_marker.png']
        ];

        $ride_delivery_vehicle_data = [];

        foreach($ride_delivery_vehicles as $ride_delivery_vehicle) {
            $ride_delivery_vehicle_data[] = [
                'ride_type_id' => $taxi_ride->id,
                'vehicle_type' => 'RIDE',
                'vehicle_name' => $ride_delivery_vehicle['name'],
                'vehicle_image' => $ride_delivery_vehicle['vehicle_image'],
                'vehicle_marker' => $ride_delivery_vehicle['vehicle_marker'],
                'company_id' => $company
            ];
        }

        $moto_delivery_vehicles = [
            ['name' => 'Sports Bike', 'vehicle_image' => url('/').'/images/transport/sports_bike.png', 'vehicle_marker' => url('/').'/images/transport/bike_marker.png'],
            ['name' => 'Scooter', 'vehicle_image' => url('/').'/images/transport/scooter.png', 'vehicle_marker' => url('/').'/images/transport/scooter_marker.png'],
            ['name' => 'Cruiser', 'vehicle_image' => url('/').'/images/transport/cruiser.png', 'vehicle_marker' => url('/').'/images/transport/bike_marker.png']
        ];

        foreach($moto_delivery_vehicles as $moto_delivery_vehicle) {
            $ride_delivery_vehicle_data[] = [
                'ride_type_id' => $moto_ride->id,
                'vehicle_type' => 'RIDE',
                'vehicle_name' => $moto_delivery_vehicle['name'],
                'vehicle_image' => $moto_delivery_vehicle['vehicle_image'],
                'vehicle_marker' => $ride_delivery_vehicle['vehicle_marker'],
                'company_id' => $company
            ];
        }

        DB::connection('transport')->table('ride_delivery_vehicles')->insert($ride_delivery_vehicle_data);

        

        $taxi = Menu::create([
            'bg_color' => '#ff9300',
            'icon' => url('/').'/images/menus/taxi.png',
            'title' => 'Taxi',
            'admin_service' => 'TRANSPORT',
            'menu_type_id' => $taxi_ride->id,
            'company_id' => $company,
            'sort_order' => 1
        ]);

        $moto = Menu::create([
            'bg_color' => '#560D0D',
            'icon' => url('/').'/images/menus/moto_ride.png',
            'title' => 'Moto Ride',
            'admin_service' => 'TRANSPORT',
            'menu_type_id' => $moto_ride->id,
            'company_id' => $company,
            'sort_order' => 1
        ]);

        

        $company_cities = CompanyCity::where('company_id', $company)->get();

        $menu_city_data = [];
        $ride_city_prices = [];
        $ride_cities = [];

        $ride_delivery_vehicles_list = DB::connection('transport')->table('ride_delivery_vehicles')->where('company_id', $company)->get();

        foreach ($company_cities as $company_city) {

            $menu_city_data[] = [
                'menu_id' => $taxi->id,
                'country_id' => $company_city->country_id,           
                'state_id' => $company_city->state_id,             
                'city_id' => $company_city->city_id,
                'status' => '1'
            ];

            $menu_city_data[] = [
                'menu_id' => $moto->id,
                'country_id' => $company_city->country_id,           
                'state_id' => $company_city->state_id,             
                'city_id' => $company_city->city_id,
                'status' => '1'
            ];

            $ride_cities[] = [
                'company_id' => $company,
                'country_id' => $company_city->country_id,           
                'city_id' => $company_city->city_id,             
                'admin_service' => 'TRANSPORT',
                'comission' => '1',
                'fleet_comission' => '1',
                'tax' => '1',
                'night_charges' => '1'
            ];


            /*foreach($ride_delivery_vehicles_list as $ride_delivery) {
                $ride_city_prices[] = [
                    'company_id' => $company,
                    'fixed' => '50',           
                    'city_id' => $company_city->city_id,             
                    'ride_delivery_vehicle_id' => $ride_delivery->id,
                    'calculator' => 'DISTANCE'
                ];
            }*/
        }
        

        if(count($menu_city_data) > 0) {
            foreach (array_chunk($menu_city_data,1000) as $menu_city_datum) {
                DB::table('menu_cities')->insert($menu_city_datum);
            }
        }

        if(count($ride_cities) > 0) {
            foreach (array_chunk($ride_cities,1000) as $ride_city) {
                DB::connection('transport')->table('ride_cities')->insert($ride_city);
            }
        }

        /*if(count($ride_city_prices) > 0) {
            foreach (array_chunk($ride_city_prices,1000) as $ride_city_price) {
                DB::connection('transport')->table('ride_city_prices')->insert($ride_city_price);
            }
        }*/
        

        $providers = Provider::where('company_id', $company)->get();

        foreach ($providers as $provider) {

            $provider_vehicle = ProviderVehicle::where('provider_id', $provider->id)->first();
            $ride_delivery = RideDeliveryVehicle::where('company_id', $company)->where('status', 1)->first();

            $provider_vehicle->vehicle_service_id = $ride_delivery->id;
            $provider_vehicle->save();

            DB::table('provider_services')->insert([
                [
                    'provider_id' => $provider->id,
                    'company_id' => $company,
                    'admin_service' => 'TRANSPORT',
                    'provider_vehicle_id' => ($provider_vehicle != null) ? $provider_vehicle->id : null,
                    'ride_delivery_id' => ($ride_delivery != null) ? $ride_delivery->id : null,
                    'category_id' => $taxi_ride->id,
                    'status' => 'ACTIVE',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            ]);

        }

	    Schema::enableForeignKeyConstraints();
    }
}
