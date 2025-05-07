<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransportDefaultDistanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('transport')->statement("Update ride_city_prices SET distance_type = 2;");
    }
}
