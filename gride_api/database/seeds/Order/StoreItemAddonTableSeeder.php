<?php

use Illuminate\Database\Seeder;

class StoreItemAddonTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($company = 1)
    {
        Schema::connection('order')->disableForeignKeyConstraints();

        $Thalapakatti  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Thalapakatti')->first()->id;
        $KFC_Demo  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','KFC Demo')->first()->id;
        $Lassi_Shop  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Lassi Shop')->first()->id;
        $SeaShell  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','SeaShell')->first()->id;
        $AlbertSons  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','AlbertSons')->first()->id;
        $Aldi  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Aldi')->first()->id;
        $Kroger_Shop  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Kroger Shop')->first()->id;
        $_Flower  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','1800 Flower')->first()->id;
        $Chowking_nigeria  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Chowking nigeria')->first()->id;
        $MOS_Burger  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','MOS Burger')->first()->id;
        $Beer_Temple  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Beer Temple')->first()->id;
        $Bonchon_Chicken  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Bonchon Chicken')->first()->id;
        $Marrybrown  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Marrybrown')->first()->id;
        $Metisse_Restaurant  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Metisse Restaurant')->first()->id;
        $Go_Cheers  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Go Cheers')->first()->id;
        $Drinkie  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Drinkie\'Z')->first()->id;
        $Cactus_Restaurant  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Cactus Restaurant')->first()->id;
        $Drankers_Park  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Dranker\'s Park')->first()->id;
        $Liquor_Palace  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Liquor Palace')->first()->id;
        $Jevinik_Restaurant  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Jevinik Restaurant')->first()->id;
        $Ferns_and_Petals  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Ferns and Petals')->first()->id;
        $Fussion_Florist  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Fussion Florist')->first()->id;
        $Just_FlowerZ  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Just FlowerZ')->first()->id;
        $Royal_Blooms  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Royal Blooms')->first()->id;
        $Flora = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Flora')->first()->id;
        $MooMix  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','MooMix')->first()->id;
        $Walmart  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Walmart')->first()->id;
        $Whole_Foods  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Whole Foods')->first()->id;
        $ShopRite  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','ShopRite')->first()->id;
        $Sala  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Sala')->first()->id;
        $Marketside  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Marketside')->first()->id;
        $Southeastern_Grocers  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Southeastern Grocers')->first()->id;
        $Food_Stuff  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Food Stuff')->first()->id;
        $Hungry_Nation  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Hungry Nation')->first()->id; 



        $Chicken_65 = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Chicken 65')->first()->id;
$Mexican_Shawarma = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Mexican Shawarma')->first()->id;
$Chicken_Fried_Rice = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Chicken Fried Rice')->first()->id;
$Mango_Juice = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Mango Juice')->first()->id;
$Chicken_Soup = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Chicken Soup')->first()->id;
$Chicken_Briyani = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Chicken Briyani')->first()->id;
$Vanilla_Milkshake = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Vanilla Milkshake')->first()->id;
$Sweet_Lassi = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Sweet Lassi')->first()->id;
$Egg_Briyani = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Egg Briyani')->first()->id;
$Fish_Fry = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Fish Fry')->first()->id;
$Grill_Chicken = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Grill Chicken')->first()->id;
$Prawn_Fry = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Prawn Fry')->first()->id;
$Veg_Soup = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Veg Soup')->first()->id;
$Chicken_Burger = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Chicken Burger')->first()->id;
$Combo_Pack = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Combo Pack')->first()->id;
$French_Fries = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','French Fries')->first()->id;
$Nuggets = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Nuggets')->first()->id;
$Pop_corn_Chicken = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Pop-corn Chicken')->first()->id;
$Strawberry_Shake = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Strawberry Shake')->first()->id;
$Butterscotch_Shake = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Butterscotch Shake')->first()->id;
$Chicken_Noodles = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Chicken Noodles')->first()->id;
$Prawn_Fry = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Prawn Fry')->first()->id;
$Mutton_Briyani = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Mutton Briyani')->first()->id;
$Lacroix_Drinks = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Lacroix Drinks')->first()->id;
$Cocacola = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Cocacola')->first()->id;
$Cheerios_ = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Cheerios')->first()->id;
$Pop_Tarts = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Pop Tarts')->first()->id;
$Belvita = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Belvita')->first()->id;
$Quaker_Oats = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Quaker Oats')->first()->id;
$Tupils_Bouquet = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Tupils Bouquet')->first()->id;
$Red_Rose_Bouquet = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Red Rose Bouquet')->first()->id;
$Bunch_Bouquet = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Bunch Bouquet')->first()->id;
$Marshmallow = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Marshmallow')->first()->id;
$American_SIngles = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','American SIngles')->first()->id;
$Cheese = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Cheese')->first()->id;
$Boneless_Chicken = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Boneless Chicken')->first()->id;
$Meat = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Meat')->first()->id;
$Tuna_Salad = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Tuna Salad')->first()->id;
$Ranch = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Ranch')->first()->id;
$Mini_Cookies = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Mini Cookies')->first()->id;
$Sugar_Cookies = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Sugar Cookies')->first()->id;
$Strawberry_Cake = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Strawberry Cake')->first()->id;
$Bacon = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Bacon')->first()->id;
$Vividly_Vanilla = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Vividly Vanilla')->first()->id;
$Air_Freshener = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Air Freshener')->first()->id;
$Shower_Cleaner = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Shower Cleaner')->first()->id;
$Floral_Embrace = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Floral Embrace')->first()->id;
$Lovely_Lavender_Medley = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Lovely Lavender Medley')->first()->id;
$Blooming_Love = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Blooming Love')->first()->id;
$Wonderful_Wishes_Bouquet = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Wonderful Wishes Bouquet')->first()->id;
$Sassy_Sweet = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Sassy nâ€™ Sweet')->first()->id;
$Assorted_Tulips = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Assorted Tulips')->first()->id;
$Healing_Tears_Blue_White = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Healing Tears Blue & White')->first()->id;
$Stunning_Red_Rose_Calla_Lily_Bouquet = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Stunning Red Rose & Calla Lily Bouquet')->first()->id;
$Chow_King_Fried_Rice = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Chow King Fried Rice')->first()->id;
$Krusty_Burger = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Krusty Burger')->first()->id;
$Varsity_Burger = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Varsity Burger')->first()->id;
$Umami_Burger = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Umami Burger')->first()->id;
$Shack_Burger = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Shack Burger')->first()->id;
$Burger_King_Whopper = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Burger King Whopper')->first()->id;
$The_21_Burger = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','The 21 Burger')->first()->id;
$In_N_Out_Burger = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','In-N-Out Burger')->first()->id;
$White_Castle_Slider = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','White Castle Slider')->first()->id;
$Gardenburger = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Gardenburger')->first()->id;
$Quadruple_Bypass_Burger = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Quadruple Bypass Burger')->first()->id;
$Mughal_Briyani = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Mughal Briyani')->first()->id;
$Hyderabadi_Biryani = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Hyderabadi Biryani')->first()->id;
$SCOFF_EE_CUP = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','SCOFF-EE CUP')->first()->id;
$DEEP_FRIED_CORN_SOUP = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','DEEP-FRIED CORN SOUP')->first()->id;
$VEGETABLE_STRIPS = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','VEGETABLE STRIPS')->first()->id;
$DIPPING_FRIES = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','DIPPING FRIES')->first()->id;
$DIPPING_FRIES = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','DIPPING FRIES')->first()->id;
$Chilled_Darjeeling_First_Flush_with_Mango = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Chilled Darjeeling First Flush with Mango')->first()->id;
$Milk_Thandai_Recipe = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Milk Thandai Recipe')->first()->id;
$Spinach_Dip_Recipe = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Spinach Dip Recipe')->first()->id;
$Irish_Coffee = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Irish Coffee')->first()->id;
$Fruit_Infused_Tea_Recipe = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Fruit Infused Tea Recipe')->first()->id;
$Portugese_Fish_Stew_Recipe = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Portugese Fish Stew Recipe')->first()->id;
$Vivid_Red_Rose_Bouquet = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Vivid-Red Rose Bouquet')->first()->id;
$Korean_Fried_Chicken = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Korean Fried Chicken')->first()->id;
$Endearing_8_Pink_Roses_Bouquet = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Endearing 8 Pink Roses Bouquet')->first()->id;
$Korean_Fried_Chicken = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Korean Fried Chicken')->first()->id;
$Pristine_White_Roses_Bunch = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Pristine White Roses Bunch')->first()->id;
$Royal__Forever_Blue_Rose_in_Velvet_Box = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Royal- Forever Blue Rose in Velvet Box')->first()->id;
$Roses_orchids_vase_arrangement = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Roses-orchids-vase-arrangement')->first()->id;
$Beautiful_Box_Of_Roses = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Beautiful Box Of Roses')->first()->id;
$Korean_Fried_Chicken_Wings = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Korean Fried Chicken Wings')->first()->id;
$Symphony_Of_Orange_Roses = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Symphony Of Orange Roses')->first()->id;
$Yellow_Roses_Bouquet_Black_Forest_Cake = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Yellow Roses Bouquet & Black Forest Cake')->first()->id;
$Red_Roses_Pineapple_Cake_Combo = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Red Roses & Pineapple Cake Combo')->first()->id;
$French_Fries_Chicken = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','French Fries Chicken')->first()->id;
$BonChon_Chicken_Bangkok = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','BonChon Chicken Bangkok')->first()->id;
$Red_Wine = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Red Wine')->first()->id;
$White_Wine = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','White Wine')->first()->id;
$Tower_Vodka = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Tower Vodka')->first()->id;
$Titos_Handmade_Vodka = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Tito\'s Handmade Vodka')->first()->id;
$Tanqueray_Gin_ = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Tanqueray Gin')->first()->id;
$Hendricks_Gin = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Hendricks Gin')->first()->id;
$Craft_Beer = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Craft Beer')->first()->id;
$Import_Beer = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Import Beer')->first()->id;
$Import_Beer = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Import Beer')->first()->id;    
$Craft_Beer = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Craft Beer')->first()->id;
$Jameson_Irish_Whiskey = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Jameson Irish Whiskey')->first()->id;
$Jim_Beam_Bourbon_Whiskey = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Jim Beam Bourbon Whiskey')->first()->id;
$Fresh_Burger = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Fresh Burger')->first()->id;
$Famous_Grouse = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Famous Grouse')->first()->id;
$Glenmorangie_The_Original = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Glenmorangie The Original')->first()->id;
$Fish_Chips = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Fish & Chips')->first()->id;
$Rose_Spirits = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Rose Spirits')->first()->id;
$Johnnie_Walker_Double_Black = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Johnnie Walker Double Black')->first()->id;
$Jim_Beam_Bourbon_Whiskey = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Jim Beam Bourbon Whiskey')->first()->id;
$Woodford_Reserve = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Woodford Reserve')->first()->id;
$Malibu_Coconut_Rum = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Malibu Coconut Rum')->first()->id;
$Sailor_Jerry_Spiced_Rum = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Sailor Jerry Spiced Rum')->first()->id;
$Cheesy_O_Burger = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Cheesy-O-Burger')->first()->id;
$Bulleit_Bourbon_ = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Bulleit Bourbon')->first()->id;
$Woodford_Reserve = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','.Woodford Reserve')->first()->id;
$Chick_A_Licious = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Chick-A-Licious')->first()->id;
$Hennessy_VS = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Hennessy VS')->first()->id;
$Hennessy_VSOP = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','.Hennessy VSOP')->first()->id;
$Clase_Azul_Reposado_Tequila = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Clase Azul Reposado Tequila')->first()->id;
$Don_Julio_Blanco_Tequila = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Don Julio Blanco Tequila')->first()->id;
$Panner_Butter_masala = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Panner Butter masala')->first()->id;
$Pasta = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Pasta')->first()->id;
$Meals = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Meals')->first()->id;
$Bubur_Ayam = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Bubur Ayam')->first()->id;
$White_Coconut_Rice = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','White Coconut Rice')->first()->id;
$Colorful_Roses = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Colorful Roses')->first()->id;
$Red_Rose_Bunch = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Red Rose Bunch')->first()->id;
$Orange_Carnation = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Orange Carnation')->first()->id;
$Red_Carnation = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Red Carnation')->first()->id;
$Red_Gerbera = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','25 Red Gerbera')->first()->id;
$Bright_Gerberas = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Bright Gerberas')->first()->id;
$Mix_Flower_Bunch = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Mix Flower Bunch')->first()->id;
$Tall_Exotic_Arrangement = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Tall Exotic Arrangement')->first()->id;
$Pink_Flowers_ = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Pink Flowers')->first()->id;
$Purple_Flowers = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Purple Flowers')->first()->id;
$Flowers_and_fruits = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Flowers and fruits')->first()->id;
$_Flowers_and_teddy = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name',' Flowers and teddy')->first()->id;
$Heart_shaped = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Heart shaped')->first()->id;
$Basket_Arrangements_ = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Basket Arrangements')->first()->id;
$Under_499 = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Under 499')->first()->id;
$five_hundred = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','500-999')->first()->id;
$Noble_Orchids = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Noble Orchids')->first()->id;
$Winter_Orchids = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Winter Orchids')->first()->id;
$Stunning_hot_lilies = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Stunning hot lilies')->first()->id;
$Blush_Pink_Lilies = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Blush Pink Lilies')->first()->id;
$Hard_soda = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Hard soda')->first()->id;
$Popcorn = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Popcorn')->first()->id;
$Snacky_Nuts = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Snacky Nuts')->first()->id;
$Bread__ = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Bread')->first()->id;
$Cooking_Oils = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Cooking Oils')->first()->id;
$Cakes__ = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Cakes ')->first()->id;
$Custard = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Custard')->first()->id;
$Ghee___ = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Ghee  ')->first()->id;
$Wheat = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Wheat')->first()->id;
$Milk__ = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Milk ')->first()->id;
$Cheese__ = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Cheese ')->first()->id;
$Soya_Milk = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Soya Milk')->first()->id;
$Cocoa_Powder_ = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Cocoa Powder')->first()->id;
$Chocolate_Chips = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Chocolate Chips')->first()->id;
$Chocolates__ = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Chocolates ')->first()->id;
$Gums = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Gums')->first()->id;
$Mouth_Freshner = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Mouth Freshner')->first()->id;
$Friut_Drinks = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Friut Drinks')->first()->id;
$Energy_Drink = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Energy Drink')->first()->id;
$Noodles = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Noodles')->first()->id;
$Pasta = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Pasta')->first()->id;
$Toor_dal = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Toor dal')->first()->id;
$Urad_dal = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Urad dal')->first()->id;
$Moong_Dall = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Moong Dall')->first()->id;
$Pickles = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Pickles ')->first()->id;
$Chutney = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Chutney')->first()->id;
$Ketchups = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Ketchups  ')->first()->id;
$Dip_Spreads = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Dip Spreads')->first()->id;
$Powdered_Spices = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Powdered Spices')->first()->id;
$Whole_Spices = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Whole Spices')->first()->id;
$Cooking_Paste = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Cooking Paste')->first()->id;
$Cashew_Nuts = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Cashew Nuts')->first()->id;
$Edible_Seeds = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Edible Seeds')->first()->id;
$Dried_Fruits = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Dried Fruits')->first()->id;
$Health_Powder_Mix = DB::connection('order')->table('store_items')->where('company_id', $company)->where('item_name','Health Powder Mix')->first()->id; 


$Onion_Thalapakatti = DB::connection('order')->table('store_addons')->where('store_id', $Thalapakatti)->where('addon_name','Onion')->first()->id;
$Sauce_Thalapakatti = DB::connection('order')->table('store_addons')->where('store_id', $Thalapakatti)->where('addon_name','Sauce')->first()->id;
$Mayonnaise_Thalapakatti = DB::connection('order')->table('store_addons')->where('store_id', $Thalapakatti)->where('addon_name','Mayonnaise')->first()->id;


$Onion_KFC_Demo = DB::connection('order')->table('store_addons')->where('store_id', $KFC_Demo)->where('addon_name','Onion')->first()->id;
$Mayonnaise_KFC_Demo = DB::connection('order')->table('store_addons')->where('store_id', $KFC_Demo)->where('addon_name','Mayonnaise')->first()->id;
$Sauce_KFC_Demo = DB::connection('order')->table('store_addons')->where('store_id', $KFC_Demo)->where('addon_name','Sauce')->first()->id;
$Cheese_KFC_Demo = DB::connection('order')->table('store_addons')->where('store_id', $KFC_Demo)->where('addon_name','Cheese')->first()->id;


$Cheese_SeaShell = DB::connection('order')->table('store_addons')->where('store_id', $SeaShell)->where('addon_name','Cheese')->first()->id;


$Sause_Lassi_Shop = DB::connection('order')->table('store_addons')->where('store_id', $Lassi_Shop)->where('addon_name','Sause')->first()->id;


$Onion_MOS_Burger = DB::connection('order')->table('store_addons')->where('store_id', $MOS_Burger)->where('addon_name','Onion')->first()->id;
$Sauce_MOS_Burger = DB::connection('order')->table('store_addons')->where('store_id', $MOS_Burger)->where('addon_name','Sauce')->first()->id;

        DB::connection('order')->table('store_item_addons')->insert([
            ['store_id' => $Thalapakatti,'store_item_id' => $Chicken_Briyani,'store_addon_id' => $Sauce_Thalapakatti,'price' =>'5.00','company_id' => $company ],
['store_id' => $Thalapakatti,'store_item_id' => $Chicken_65,'store_addon_id' => $Onion_Thalapakatti,'price' =>'10.00','company_id' => $company ],
['store_id' => $Thalapakatti,'store_item_id' => $Chicken_65,'store_addon_id' => $Sauce_Thalapakatti,'price' =>'10.00','company_id' => $company ],
['store_id' => $Thalapakatti,'store_item_id' => $Chicken_65,'store_addon_id' => $Mayonnaise_Thalapakatti,'price' =>'10.00','company_id' => $company ],
['store_id' => $Thalapakatti,'store_item_id' => $Egg_Briyani,'store_addon_id' => $Onion_Thalapakatti,'price' =>'5.00','company_id' => $company ],
['store_id' => $Thalapakatti,'store_item_id' => $Fish_Fry,'store_addon_id' => $Onion_Thalapakatti,'price' =>'5.00','company_id' => $company ],
['store_id' => $Thalapakatti,'store_item_id' => $Fish_Fry,'store_addon_id' => $Sauce_Thalapakatti,'price' =>'5.00','company_id' => $company ],
['store_id' => $Thalapakatti,'store_item_id' => $Grill_Chicken,'store_addon_id' => $Onion_Thalapakatti,'price' =>'5.00','company_id' => $company ],
['store_id' => $Thalapakatti,'store_item_id' => $Grill_Chicken,'store_addon_id' => $Sauce_Thalapakatti,'price' =>'5.00','company_id' => $company ],
['store_id' => $Thalapakatti,'store_item_id' => $Grill_Chicken,'store_addon_id' => $Mayonnaise_Thalapakatti,'price' =>'5.00','company_id' => $company ],
['store_id' => $Thalapakatti,'store_item_id' => $Prawn_Fry,'store_addon_id' => $Onion_Thalapakatti,'price' =>'5.00','company_id' => $company ],
['store_id' => $KFC_Demo,'store_item_id' => $Mexican_Shawarma,'store_addon_id' => $Mayonnaise_KFC_Demo,'price' =>'5.00','company_id' => $company ],
['store_id' => $KFC_Demo,'store_item_id' => $Mexican_Shawarma,'store_addon_id' => $Sauce_KFC_Demo,'price' =>'5.00','company_id' => $company ],
['store_id' => $KFC_Demo,'store_item_id' => $Chicken_Burger,'store_addon_id' => $Sauce_KFC_Demo,'price' =>'5.00','company_id' => $company ],
['store_id' => $KFC_Demo,'store_item_id' => $Pop_corn_Chicken,'store_addon_id' => $Onion_KFC_Demo,'price' =>'5.00','company_id' => $company ],
['store_id' => $KFC_Demo,'store_item_id' => $Pop_corn_Chicken,'store_addon_id' => $Sauce_KFC_Demo,'price' =>'5.00','company_id' => $company ],

['store_id' => $SeaShell,'store_item_id' => $Chicken_Fried_Rice,'store_addon_id' => $Cheese_SeaShell,'price' =>'3.00','company_id' => $company ],
['store_id' => $Thalapakatti,'store_item_id' => $Chicken_Soup,'store_addon_id' => $Sauce_Thalapakatti,'price' =>'3.00','company_id' => $company ],
['store_id' => $KFC_Demo,'store_item_id' => $French_Fries,'store_addon_id' => $Onion_KFC_Demo,'price' =>'20.00','company_id' => $company ],
['store_id' => $MOS_Burger,'store_item_id' => $Krusty_Burger,'store_addon_id' => $Onion_MOS_Burger,'price' =>'10.00','company_id' => $company ],
['store_id' => $MOS_Burger,'store_item_id' => $Varsity_Burger,'store_addon_id' => $Onion_MOS_Burger,'price' =>'15.00','company_id' => $company ],
['store_id' => $MOS_Burger,'store_item_id' => $Umami_Burger,'store_addon_id' => $Onion_MOS_Burger,'price' =>'10.00','company_id' => $company ],
['store_id' => $MOS_Burger,'store_item_id' => $Shack_Burger,'store_addon_id' => $Onion_MOS_Burger,'price' =>'10.00','company_id' => $company ],
['store_id' => $MOS_Burger,'store_item_id' => $Burger_King_Whopper,'store_addon_id' => $Onion_MOS_Burger,'price' =>'10.00','company_id' => $company ],
['store_id' => $MOS_Burger,'store_item_id' => $The_21_Burger,'store_addon_id' => $Onion_MOS_Burger,'price' =>'10.00','company_id' => $company ],
['store_id' => $MOS_Burger,'store_item_id' => $In_N_Out_Burger,'store_addon_id' => $Onion_MOS_Burger,'price' =>'10.00','company_id' => $company ],
['store_id' => $MOS_Burger,'store_item_id' => $White_Castle_Slider,'store_addon_id' => $Onion_MOS_Burger,'price' =>'10.00','company_id' => $company ],
['store_id' => $MOS_Burger,'store_item_id' => $Gardenburger,'store_addon_id' => $Onion_MOS_Burger,'price' =>'10.00','company_id' => $company ],
['store_id' => $MOS_Burger,'store_item_id' => $Quadruple_Bypass_Burger,'store_addon_id' => $Onion_MOS_Burger,'price' =>'10.00','company_id' => $company ],
['store_id' => $Thalapakatti,'store_item_id' => $Mughal_Briyani,'store_addon_id' => $Onion_Thalapakatti,'price' =>'20.00','company_id' => $company ],
['store_id' => $Thalapakatti,'store_item_id' => $Hyderabadi_Biryani,'store_addon_id' => $Onion_Thalapakatti,'price' =>'10.00','company_id' => $company ],
['store_id' => $KFC_Demo,'store_item_id' => $SCOFF_EE_CUP,'store_addon_id' => $Onion_KFC_Demo,'price' =>'10.00','company_id' => $company ],
['store_id' => $KFC_Demo,'store_item_id' => $SCOFF_EE_CUP,'store_addon_id' => $Mayonnaise_KFC_Demo,'price' =>'10.00','company_id' => $company ],
['store_id' => $KFC_Demo,'store_item_id' => $DEEP_FRIED_CORN_SOUP,'store_addon_id' => $Onion_KFC_Demo,'price' =>'10.00','company_id' => $company ],
['store_id' => $KFC_Demo,'store_item_id' => $DEEP_FRIED_CORN_SOUP,'store_addon_id' => $Sauce_KFC_Demo,'price' =>'10.00','company_id' => $company ],
['store_id' => $KFC_Demo,'store_item_id' => $VEGETABLE_STRIPS,'store_addon_id' => $Onion_KFC_Demo,'price' =>'10.00','company_id' => $company ],
['store_id' => $KFC_Demo,'store_item_id' => $VEGETABLE_STRIPS,'store_addon_id' => $Sauce_KFC_Demo,'price' =>'10.00','company_id' => $company ],
['store_id' => $KFC_Demo,'store_item_id' => $DIPPING_FRIES,'store_addon_id' => $Onion_KFC_Demo,'price' =>'10.00','company_id' => $company ],
['store_id' => $KFC_Demo,'store_item_id' => $DIPPING_FRIES,'store_addon_id' => $Mayonnaise_KFC_Demo,'price' =>'12.00','company_id' => $company ]
        ]);

        Schema::connection('order')->enableForeignKeyConstraints();
    }
}
