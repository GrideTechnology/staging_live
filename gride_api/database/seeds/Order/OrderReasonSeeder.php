<?php

use Illuminate\Database\Seeder;

class OrderReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($company = 1)
    {
        Schema::disableForeignKeyConstraints();

        DB::table('reasons')->insert([
            ['company_id' => $company, 'service' => 'ORDER', 'type' =>'USER', 'reason' => 'Extra amount charged', 'status' => 'Active'],
            ['company_id' => $company, 'service' => 'ORDER', 'type' =>'PROVIDER', 'reason' => 'Delivery Person changed the order', 'status' => 'Active'],
            ['company_id' => $company, 'service' => 'ORDER', 'type' =>'USER', 'reason' => 'Delivery person delayed pickup', 'status' => 'Active']
        ]);
        
        Schema::enableForeignKeyConstraints();
    }
}
