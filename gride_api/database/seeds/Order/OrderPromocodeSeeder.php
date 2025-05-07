<?php

use Illuminate\Database\Seeder;

class OrderPromocodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($company = 1)
    {
        Schema::disableForeignKeyConstraints();

        DB::table('promocodes')->insert([
            ['company_id' => $company, 'promo_code' => 'Food', 'service' =>'ORDER', 'picture' => url('/').'/images/common/promocodes/order.png', 'percentage' => '5.00', 'max_amount' => '90.00', 'promo_description' => '5% off, Max discount is 90', 'expiration' => '2019-11-15', 'status' => 'ADDED']
        ]);
        
        Schema::enableForeignKeyConstraints();
    }
}
