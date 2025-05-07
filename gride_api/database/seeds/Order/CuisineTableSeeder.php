<?php

use Illuminate\Database\Seeder;

class CuisineTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($company = 1)
    {
        Schema::connection('order')->disableForeignKeyConstraints();


        $Foodie = DB::connection('order')->table('store_types')->where('name', 'Foodie')->first()->id;
        $Grocery = DB::connection('order')->table('store_types')->where('name', 'Grocery')->first()->id;
        $Alcohol = DB::connection('order')->table('store_types')->where('name', 'Alcohol')->first()->id;
        $Flower = DB::connection('order')->table('store_types')->where('name', 'Flower')->first()->id;

        DB::connection('order')->table('cuisines')->insert([
            ['store_type_id' => $Foodie, 'name' => 'North Indian Foods', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Foodie, 'name' => 'South Indian Foods', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Foodie, 'name' => 'American Foods', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Foodie, 'name' => 'Arabian Foods', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Foodie, 'name' => 'Bakers', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Foodie, 'name' => 'Asian', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Foodie, 'name' => 'African Food', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Grocery, 'name' => 'Beverages', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Grocery, 'name' => 'Dairy', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Alcohol, 'name' => 'Bacon Beer Cornbread', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Alcohol, 'name' => 'Boozy Sausage', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Alcohol, 'name' => 'Chips and Guac', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Alcohol, 'name' => 'Cheez Its Boxed Wine', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Alcohol, 'name' => 'Tandoori Chicken Bites', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Alcohol, 'name' => 'Cheese Stuffed Chicken ', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Alcohol, 'name' => 'French Fries and Potato Chips', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Alcohol, 'name' => 'Tandoori Chicken', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Alcohol, 'name' => 'Paneer Tikka', 'status' =>'USER', 'company_id' => $company, 'status' => 1],
            ['store_type_id' => $Alcohol, 'name' => 'Chicken wing', 'status' =>'USER', 'company_id' => $company, 'status' => 1]
        ]);

        
        Schema::connection('order')->enableForeignKeyConstraints();
    }
}