<?php

use Illuminate\Database\Seeder;

class TransportDisputeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($company = 1)
    {
        Schema::disableForeignKeyConstraints();

        DB::table('disputes')->insert([
            ['service' => 'TRANSPORT', 'dispute_type' => 'user', 'dispute_name' => 'Provider rude and arrogant', 'status' =>'active', 'admin_services' => 'TRANSPORT', 'company_id' =>$company],
            ['service' => 'TRANSPORT', 'dispute_type' => 'provider', 'dispute_name' => 'Customer arrogant and rude', 'status' =>'active', 'admin_services' => 'TRANSPORT', 'company_id' =>$company],
            ['service' => 'TRANSPORT', 'dispute_type' => 'user', 'dispute_name' => 'Provider Asked Extra Amount', 'status' =>'active', 'admin_services' => 'TRANSPORT', 'company_id' =>$company],
            ['service' => 'TRANSPORT', 'dispute_type' => 'provider', 'dispute_name' => 'User entered  wrong destination', 'status' =>'active', 'admin_services' => 'TRANSPORT', 'company_id' =>$company],
            ['service' => 'TRANSPORT', 'dispute_type' => 'user', 'dispute_name' => 'My Promocode does not get applied', 'status' =>'active', 'admin_services' => 'TRANSPORT', 'company_id' =>$company],
            ['service' => 'TRANSPORT', 'dispute_type' => 'user', 'dispute_name' => 'Driver followed wrong route', 'status' =>'active', 'admin_services' => 'TRANSPORT', 'company_id' =>$company],
            ['service' => 'TRANSPORT', 'dispute_type' => 'provider', 'dispute_name' => 'User changed multiple destination', 'status' =>'active', 'admin_services' => 'TRANSPORT', 'company_id' =>$company]      
        ]);
        
        Schema::enableForeignKeyConstraints();
    }
}
