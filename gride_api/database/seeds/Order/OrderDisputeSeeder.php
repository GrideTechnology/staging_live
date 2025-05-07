<?php

use Illuminate\Database\Seeder;

class OrderDisputeSeeder extends Seeder
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
            ['service' => 'ORDER', 'dispute_type' => 'user', 'dispute_name' => 'Delivery person Asked Extra Amount', 'status' =>'active', 'admin_services' => 'ORDER', 'company_id' =>$company],
            ['service' => 'ORDER', 'dispute_type' => 'user', 'dispute_name' => 'My Promocode Does Not Get Applied', 'status' =>'active', 'admin_services' => 'ORDER', 'company_id' =>$company],
            ['service' => 'ORDER', 'dispute_type' => 'provider', 'dispute_name' => 'User provided wrong delivery address', 'status' =>'active', 'admin_services' => 'ORDER', 'company_id' =>$company],
            ['service' => 'ORDER', 'dispute_type' => 'user', 'dispute_name' => 'Delivery person picked wrong order and delivered', 'status' =>'active', 'admin_services' => 'ORDER', 'company_id' =>$company],
            ['service' => 'ORDER', 'dispute_type' => 'user', 'dispute_name' => 'My Promocode does not get applied', 'status' =>'active', 'admin_services' => 'ORDER', 'company_id' =>$company],
            ['service' => 'ORDER', 'dispute_type' => 'provider', 'dispute_name' => 'User changed the order', 'status' =>'active', 'admin_services' => 'ORDER', 'company_id' =>$company],
            ['service' => 'ORDER', 'dispute_type' => 'provider', 'dispute_name' => 'User not available to pick the order', 'status' =>'active', 'admin_services' => 'ORDER', 'company_id' =>$company],
            ['service' => 'ORDER', 'dispute_type' => 'user', 'dispute_name' => 'Delivery person delayed pickup', 'status' =>'active', 'admin_services' => 'ORDER', 'company_id' =>$company],
            ['service' => 'ORDER', 'dispute_type' => 'provider', 'dispute_name' => 'User not reachable', 'status' =>'active', 'admin_services' => 'ORDER', 'company_id' =>$company]      
        ]);
        
        Schema::enableForeignKeyConstraints();
    }
}
