<?php

use Illuminate\Database\Seeder;

class StoreCuisineTableSeeder extends Seeder
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

        $Thalapakatti  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Thalapakatti')->first()->id;
        $KFC_Demo  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'KFC Demo')->first()->id;
        $Lassi_Shop  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Lassi Shop')->first()->id;
        $SeaShell  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'SeaShell')->first()->id;
        $AlbertSons  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'AlbertSons')->first()->id;
        $Aldi  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Aldi')->first()->id;
        $Kroger_Shop  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Kroger Shop')->first()->id;
        $_Flower  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', '1800 Flower')->first()->id;
        $Chowking_nigeria  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Chowking nigeria')->first()->id;
        $MOS_Burger  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'MOS Burger')->first()->id;
        $Beer_Temple  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Beer Temple')->first()->id;
        $Bonchon_Chicken  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Bonchon Chicken')->first()->id;
        $Marrybrown  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Marrybrown')->first()->id;
        $Metisse_Restaurant  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Metisse Restaurant')->first()->id;
        $Go_Cheers  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Go Cheers')->first()->id;
        $Drinkie  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Drinkie\'Z')->first()->id;
        $Cactus_Restaurant  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Cactus Restaurant')->first()->id;
        $Drankers_Park  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Dranker\'s Park')->first()->id;
        $Liquor_Palace  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Liquor Palace')->first()->id;
        $Jevinik_Restaurant  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Jevinik Restaurant')->first()->id;
        $Ferns_and_Petals  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Ferns and Petals')->first()->id;
        $Fussion_Florist  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Fussion Florist')->first()->id;
        $Just_FlowerZ  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Just FlowerZ')->first()->id;
        $Royal_Blooms  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Royal Blooms')->first()->id;
        $Flora = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Flora')->first()->id;
        $MooMix  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'MooMix')->first()->id;
        $Walmart  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Walmart')->first()->id;
        $Whole_Foods  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Whole Foods')->first()->id;
        $ShopRite  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'ShopRite')->first()->id;
        $Sala  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Sala')->first()->id;
        $Marketside  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Marketside')->first()->id;
        $Southeastern_Grocers  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Southeastern Grocers')->first()->id;
        $Food_Stuff  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Food Stuff')->first()->id;
        $Hungry_Nation  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Hungry Nation')->first()->id; 

        $North_Indian_Foods = DB::connection('order')->table('cuisines')->where('name', 'North Indian Foods')->where('company_id', $company)->where('store_type_id', $Foodie)->first()->id;
$South_Indian_Foods = DB::connection('order')->table('cuisines')->where('name', 'South Indian Foods')->where('company_id', $company)->where('store_type_id', $Foodie)->first()->id;
$American_Foods = DB::connection('order')->table('cuisines')->where('name', 'American Foods')->where('company_id', $company)->where('store_type_id', $Foodie)->first()->id;
$Arabian_Foods = DB::connection('order')->table('cuisines')->where('name', 'Arabian Foods')->where('company_id', $company)->where('store_type_id', $Foodie)->first()->id;
$Bakers = DB::connection('order')->table('cuisines')->where('name', 'Bakers')->where('company_id', $company)->where('store_type_id', $Foodie)->first()->id;
$Asian = DB::connection('order')->table('cuisines')->where('name', 'Asian')->where('company_id', $company)->where('store_type_id', $Foodie)->first()->id;
$African_Food = DB::connection('order')->table('cuisines')->where('name', 'African Food')->where('company_id', $company)->where('store_type_id', $Foodie)->first()->id;
$Beverages = DB::connection('order')->table('cuisines')->where('name', 'Beverages')->where('company_id', $company)->where('store_type_id', $Grocery)->first();

$Dairy = DB::connection('order')->table('cuisines')->where('name', 'Dairy')->where('company_id', $company)->where('store_type_id', $Grocery)->first()->id;
$Bacon_Beer_Cornbread = DB::connection('order')->table('cuisines')->where('name', 'Bacon Beer Cornbread')->where('company_id', $company)->where('store_type_id', $Alcohol)->first()->id;
$Boozy_Sausage = DB::connection('order')->table('cuisines')->where('name', 'Boozy Sausage')->where('company_id', $company)->where('store_type_id', $Alcohol)->first()->id;
$Chips_and_Guac = DB::connection('order')->table('cuisines')->where('name', 'Chips and Guac')->where('company_id', $company)->where('store_type_id', $Alcohol)->first()->id;
$Cheez_Its_Boxed_Wine_ = DB::connection('order')->table('cuisines')->where('name', 'Cheez Its Boxed Wine')->where('company_id', $company)->where('store_type_id', $Alcohol)->first()->id;
$Tandoori_Chicken_Bites = DB::connection('order')->table('cuisines')->where('name', 'Tandoori Chicken Bites')->where('company_id', $company)->where('store_type_id', $Alcohol)->first()->id;
$Cheese_Stuffed_Chicken_ = DB::connection('order')->table('cuisines')->where('name', 'Cheese Stuffed Chicken ')->where('company_id', $company)->where('store_type_id', $Alcohol)->first()->id;
$French_Fries_and_Potato_Chips = DB::connection('order')->table('cuisines')->where('name', 'French Fries and Potato Chips')->where('company_id', $company)->where('store_type_id', $Alcohol)->first()->id;
$Tandoori_Chicken = DB::connection('order')->table('cuisines')->where('name', 'Tandoori Chicken')->where('company_id', $company)->where('store_type_id', $Alcohol)->first()->id;
$Paneer_Tikka = DB::connection('order')->table('cuisines')->where('name', 'Paneer Tikka')->where('company_id', $company)->where('store_type_id', $Alcohol)->first()->id;
$Chicken_wing = DB::connection('order')->table('cuisines')->where('name', 'Chicken wing')->where('company_id', $company)->where('store_type_id', $Alcohol)->first()->id;

        


        DB::connection('order')->table('store_cuisines')->insert([
            ['store_type_id' => $Foodie, 'store_id' => $Thalapakatti, 'cuisines_id' => $North_Indian_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Thalapakatti, 'cuisines_id' => $South_Indian_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Thalapakatti, 'cuisines_id' => $American_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Thalapakatti, 'cuisines_id' => $Bakers, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Thalapakatti, 'cuisines_id' => $Asian, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Chowking_nigeria, 'cuisines_id' => $Asian, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $MOS_Burger, 'cuisines_id' => $North_Indian_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $MOS_Burger, 'cuisines_id' => $South_Indian_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $MOS_Burger, 'cuisines_id' => $American_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Bonchon_Chicken, 'cuisines_id' => $North_Indian_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Bonchon_Chicken, 'cuisines_id' => $South_Indian_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Marrybrown, 'cuisines_id' => $North_Indian_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Marrybrown, 'cuisines_id' => $South_Indian_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Metisse_Restaurant, 'cuisines_id' => $North_Indian_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Metisse_Restaurant, 'cuisines_id' => $South_Indian_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Cactus_Restaurant, 'cuisines_id' => $North_Indian_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Cactus_Restaurant, 'cuisines_id' => $South_Indian_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Jevinik_Restaurant, 'cuisines_id' => $American_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Jevinik_Restaurant, 'cuisines_id' => $Bakers, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Sala, 'cuisines_id' => $North_Indian_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Lassi_Shop, 'cuisines_id' => $Bakers, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $KFC_Demo, 'cuisines_id' => $North_Indian_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $SeaShell, 'cuisines_id' => $American_Foods, 'company_id' => $company],
            ['store_type_id' => $Foodie, 'store_id' => $Hungry_Nation, 'cuisines_id' => $African_Food, 'company_id' => $company]
        ]);
        
        Schema::connection('order')->enableForeignKeyConstraints();
    }
}