<?php

use Illuminate\Database\Seeder;

class StoreCategoryTableSeeder extends Seeder
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

        

        DB::connection('order')->table('store_categories')->insert([
            ['store_id' => $Thalapakatti,'store_category_name' =>'Veg','store_category_description' =>'Vegetarian','store_category_status' => 1,'company_id' => $company],
['store_id' => $Thalapakatti,'store_category_name' =>'Non-Vegetarian','store_category_description' =>'Non-Vegetarian','store_category_status' => 1,'company_id' => $company],
['store_id' => $KFC_Demo,'store_category_name' =>'Non-Vegetarian','store_category_description' =>'Non-Vegetarian','store_category_status' => 1,'company_id' => $company],
['store_id' => $Lassi_Shop,'store_category_name' =>'Vegetarian','store_category_description' =>'Vegetarian','store_category_status' => 1,'company_id' => $company],
['store_id' => $SeaShell,'store_category_name' =>'Burger','store_category_description' =>'Yummy','store_category_status' => 1,'company_id' => $company],
['store_id' => $AlbertSons,'store_category_name' =>'Beverages','store_category_description' =>'Cool Drinks','store_category_status' => 1,'company_id' => $company],
['store_id' => $AlbertSons,'store_category_name' =>'Breakfast and cereal','store_category_description' =>'Breakfast','store_category_status' => 1,'company_id' => $company],
['store_id' => $AlbertSons,'store_category_name' =>'Flowers','store_category_description' =>'Flowers','store_category_status' => 1,'company_id' => $company],
['store_id' => $Aldi,'store_category_name' =>'Diary','store_category_description' =>'Diary','store_category_status' => 1,'company_id' => $company],
['store_id' => $Aldi,'store_category_name' =>'Meat','store_category_description' =>'Meat','store_category_status' => 1,'company_id' => $company],
['store_id' => $Aldi,'store_category_name' =>'Pantry','store_category_description' =>'Pantry','store_category_status' => 1,'company_id' => $company],
['store_id' => $Kroger_Shop,'store_category_name' =>'Bakery','store_category_description' =>'Bakery','store_category_status' => 1,'company_id' => $company],
['store_id' => $Kroger_Shop,'store_category_name' =>'Frozen','store_category_description' =>'Frozen','store_category_status' => 1,'company_id' => $company],
['store_id' => $Kroger_Shop,'store_category_name' =>'HouseHold','store_category_description' =>'HouseHold','store_category_status' => 1,'company_id' => $company],
['store_id' => $_Flower,'store_category_name' =>'Same Day Flowers','store_category_description' =>'Same Day Flowers','store_category_status' => 1,'company_id' => $company],
['store_id' => $_Flower,'store_category_name' =>'Birthday','store_category_description' =>'Wishes','store_category_status' => 1,'company_id' => $company],
['store_id' => $_Flower,'store_category_name' =>'Occasions','store_category_description' =>'Occasions','store_category_status' => 1,'company_id' => $company],
['store_id' => $Chowking_nigeria,'store_category_name' =>'Rice','store_category_description' =>'Rice','store_category_status' => 1,'company_id' => $company],
['store_id' => $MOS_Burger,'store_category_name' =>'Veg','store_category_description' =>'Vegetarian','store_category_status' => 1,'company_id' => $company],
['store_id' => $Beer_Temple,'store_category_name' =>'Wine','store_category_description' =>'Wines with high acidity taste tart and zesty. Red wines have more tart fruit characteristics (versus â€œsweet fruitâ€). White wines are often described with characteristics similar to lemon or lime juice.','store_category_status' => 1,'company_id' => $company],
['store_id' => $Beer_Temple,'store_category_name' =>'Vodka','store_category_description' =>'Vodka is the most common distilled spirit found in cocktails and mixed drinks and it is essential to every bar. Its popularity comes from the general characteristic that it has no discernible or distinct flavor or smell and it is often clear.','store_category_status' => 1,'company_id' => $company],
['store_id' => $Beer_Temple,'store_category_name' =>'Gin','store_category_description' =>'Gin is a colorless spirit obtained by distilling an aqueous mixture of alcohol together with aromatic plant materials, generally juniper berries (Juniperus communis L.), to which water and alcohol and at times fruit juices, extracts, and/or essential oils','store_category_status' => 1,'company_id' => $company],
['store_id' => $Bonchon_Chicken,'store_category_name' =>'Non-Veg','store_category_description' =>'Non-Vegetarian','store_category_status' => 1,'company_id' => $company],
['store_id' => $Ferns_and_Petals,'store_category_name' =>'Roses','store_category_description' =>'Aromatic Roses','store_category_status' => 1,'company_id' => $company],
['store_id' => $Ferns_and_Petals,'store_category_name' =>'Premium Flowers','store_category_description' =>'Fresh Flowers','store_category_status' => 1,'company_id' => $company],
['store_id' => $Ferns_and_Petals,'store_category_name' =>'Flowers N Cakes','store_category_description' =>'Fresh Flowers and cakes','store_category_status' => 1,'company_id' => $company],
['store_id' => $Go_Cheers,'store_category_name' =>'Beer','store_category_description' =>'beer â€“ formerly known as â€œBass Finestâ€ â€“ pours a ruby colour with a white, thinnish head. The nose is quite subdued, with nettle and light malt notes and a nutty character. On the palate it is quite fresh and fruity, with moderate bitterness and a','store_category_status' => 1,'company_id' => $company],
['store_id' => $Go_Cheers,'store_category_name' =>'Whisky','store_category_description' =>'Kentucky- Smooth and approachable with an easy finish, a true contrast to hot, harsh whiskies that "blow your ears off" and a downright revolutionary idea at the time. Maker\'s Mark is made slowly in small batches, in our National Historic Landmark distill','store_category_status' => 1,'company_id' => $company],
['store_id' => $Drankers_Park,'store_category_name' =>'Brandy','store_category_description' =>'One of the most distinctive qualities of E & J Brandy is its remarkable character. This is accomplished by vertical blending of brandies of different ages from the finest white oak barrels. This aging process also develops the full and natural brandy flav','store_category_status' => 1,'company_id' => $company],
['store_id' => $Drankers_Park,'store_category_name' =>'Tequila','store_category_description' =>'Tequila is a popular distilled spirit that is rich in history, far beyond the popular margarita or tequila shot. Originally used during rituals beginning 2,000 years ago, tequila has evolved into the potent spirit we drink today. In recent years, it has t','store_category_status' => 1,'company_id' => $company],
['store_id' => $Liquor_Palace,'store_category_name' =>'Rum','store_category_description' =>'Barbados- Made in the spirit of traditional Caribbean flavored rums and filled with the fresh, natural taste of coconut, this smooth-drinking rum infuses any drink with a taste of the tropics. Perfect on its own or as a refreshing splash in any rum-based','store_category_status' => 1,'company_id' => $company],
['store_id' => $Liquor_Palace,'store_category_name' =>'Bacardi Limon','store_category_description' =>'The original modern icon from Bacardi. It\'s chic, adult, innovative, and desirable. Bacardi has crafted Limon in honor of the long-lasting tradition of drinking BACARDI rums on the rocks with lime.','store_category_status' => 1,'company_id' => $company],
['store_id' => $Drinkie,'store_category_name' =>'Spirits','store_category_description' =>'Crystal clear, pure ultra premium. Light, fresh spirits is a favorite of connoisseurs worldwide. Smooth, soft, light tequila. The perfect ingredient in special margaritas or mixed cocktails, neat or on the rocks.','store_category_status' => 1,'company_id' => $company],
['store_id' => $Drinkie,'store_category_name' =>'Bourbon','store_category_description' =>'The dry, clean flavor is mellow and spice with rye, not hot in the throat. This 90-proof Kentucky bourbon delivers a wonderfully complex taste with hints of vanilla and honey and a long smoky finish.','store_category_status' => 1,'company_id' => $company],
['store_id' => $Beer_Temple,'store_category_name' =>'Beer','store_category_description' =>'beer â€“ formerly known as â€œBass Finestâ€ â€“ pours a ruby colour with a white, thinnish head. The nose is quite subdued, with nettle and light malt notes and a nutty character. On the palate it is quite fresh and fruity, with moderate bitterness and a','store_category_status' => 1,'company_id' => $company],
['store_id' => $Go_Cheers,'store_category_name' =>'Scotch','store_category_description' =>'The original Walker family blend, handcrafted from as many as 40 of the finest Scotch whiskies aged a minimum of 12 years, for a smooth and robust blend. Rich smoky malt, peat and sherry fruit character deliver a satisfyingly complex flavor on the long, l','store_category_status' => 1,'company_id' => $company],
['store_id' => $Marrybrown,'store_category_name' =>'Veg','store_category_description' =>'Vegetarian','store_category_status' => 1,'company_id' => $company],
['store_id' => $Marrybrown,'store_category_name' =>'Non-Veg','store_category_description' =>'Non-Vegetarian','store_category_status' => 1,'company_id' => $company],
['store_id' => $Metisse_Restaurant,'store_category_name' =>'Veg','store_category_description' =>'Vegetarian','store_category_status' => 1,'company_id' => $company],
['store_id' => $Cactus_Restaurant,'store_category_name' =>'Veg','store_category_description' =>'Vegetarian','store_category_status' => 1,'company_id' => $company],
['store_id' => $Cactus_Restaurant,'store_category_name' =>'Non-Veg','store_category_description' =>'Non-Vegetarian','store_category_status' => 1,'company_id' => $company],
['store_id' => $Jevinik_Restaurant,'store_category_name' =>'Non-Veg','store_category_description' =>'Non-vegetarian','store_category_status' => 1,'company_id' => $company],
['store_id' => $Fussion_Florist,'store_category_name' =>'Roses','store_category_description' =>'A small token of endless  love as roses bunch to gratify the person you wish to','store_category_status' => 1,'company_id' => $company],
['store_id' => $Fussion_Florist,'store_category_name' =>'Carnations','store_category_description' =>'Dianthus caryophyllus, Pink carnations have the most symbolic and historical significance. Acco... ','store_category_status' => 1,'company_id' => $company],
['store_id' => $Just_FlowerZ,'store_category_name' =>'Gerberas','store_category_description' =>'Bunch of  Mix Color Flowers.','store_category_status' => 1,'company_id' => $company],
['store_id' => $Just_FlowerZ,'store_category_name' =>'Mix Flowers','store_category_description' =>'A Colourful wish of 20 mixed Flowers','store_category_status' => 1,'company_id' => $company],
['store_id' => $Royal_Blooms,'store_category_name' =>'Flowers By Colors','store_category_description' =>'Various colors of flowers','store_category_status' => 1,'company_id' => $company],
['store_id' => $Royal_Blooms,'store_category_name' =>'Flowers By Combo','store_category_description' =>'Various Combos of flowers.','store_category_status' => 1,'company_id' => $company],
['store_id' => $Flora,'store_category_name' =>'Flowers By Design','store_category_description' =>'Bundly of lovely designs','store_category_status' => 1,'company_id' => $company],
['store_id' => $Flora,'store_category_name' =>'Flowers By Price','store_category_description' =>'Lower and higher price flowers','store_category_status' => 1,'company_id' => $company],
['store_id' => $MooMix,'store_category_name' =>'Orchids','store_category_description' =>'most highly evolved of all flowering plants.','store_category_status' => 1,'company_id' => $company],
['store_id' => $MooMix,'store_category_name' =>'Lilies','store_category_description' =>'really excellent plants for beds and borders. Lilies are suitable for use in a shrub border, as accent plants, a formal or naturalized pool planting','store_category_status' => 1,'company_id' => $company],
['store_id' => $Walmart,'store_category_name' =>'Beverages','store_category_description' =>'A drink is a liquid intended for human consumption. In addition to their basic function of satisfying thirst, drinks play important roles in human culture','store_category_status' => 1,'company_id' => $company],
['store_id' => $Walmart,'store_category_name' =>'Bread/Bakery','store_category_description' =>'Bakery products, particularly bread, have a long history of development. The biochemistry of the main components of wheat flour (proteins, carbohydrates, and lipids) are presented and discussed with a focus on those properties relevant to the baking indus','store_category_status' => 1,'company_id' => $company],
['store_id' => $Walmart,'store_category_name' =>'Staples','store_category_description' =>'Products purchased regularly and out of necessity are considered "staple goods" to your store. Traditionally, these items have fewer markdowns and sometimes lower profit margins. While price shifts may raise or lower demand for certain kinds of products,','store_category_status' => 1,'company_id' => $company],
['store_id' => $Whole_Foods,'store_category_name' =>'Dairy','store_category_description' =>'Dairy products, milk products or lacticinia are a type of food produced from or containing the milk of mammals. They are primarily produced from mammals such as cattle, water buffaloes, goats, sheep, camels and humans','store_category_status' => 1,'company_id' => $company],
['store_id' => $Whole_Foods,'store_category_name' =>'Dry/Baking Goods','store_category_description' =>'Baked goods are cooked by baking, a method ofcooking food that uses prolonged dry heat, normally in an oven, but also in hot ashes, or on hot stones. The most common baked item is bread but many other types of foods are baked as well.','store_category_status' => 1,'company_id' => $company],
['store_id' => $ShopRite,'store_category_name' =>'Chocolates & Sweets','store_category_description' =>'Candy, also called sweets or lollies, is a confection that features sugar as a principal ingredient. The category, called sugar confectionery, encompasses any sweet confection, including chocolate, chewing gum, and sugar candy','store_category_status' => 1,'company_id' => $company],
['store_id' => $ShopRite,'store_category_name' =>'Soft Drinks','store_category_description' =>'The sweetener may be a sugar, high-fructose corn syrup, fruit juice, a sugar substitute (in the case of diet drinks), or some combination of these. Soft drinks may also contain caffeine, colorings, preservatives, and/or other ingredients. Soft drinks are','store_category_status' => 1,'company_id' => $company],
['store_id' => $ShopRite,'store_category_name' =>'Packaged food','store_category_description' =>'The majority of your diet should still be made up of real, whole foodsâ€”think vegetables, fruits, whole grains, proteins. Packaged foods get a bad rap for their long ingredient lists, trans fats and sodium, but not all packaged foods are created equal','store_category_status' => 1,'company_id' => $company],
['store_id' => $Marketside,'store_category_name' =>'Dals and Pulses','store_category_description' =>'Dal is a term used in the Indian subcontinent for dried, split pulses. The term is also used for various soups prepared from these pulses. These pulses are among the most important staple foods in South Asian countries, and form an important part of the c','store_category_status' => 1,'company_id' => $company],
['store_id' => $Marketside,'store_category_name' =>'Pickles & Chutney','store_category_description' =>'Some pickles and chutneys in India has been passed over to commercial production, whereas at one time it was done entirely in people\'s homes. The disadvantage of commercial chutneys and those produced in western style with vinegar and large amounts of sug','store_category_status' => 1,'company_id' => $company],
['store_id' => $Marketside,'store_category_name' =>'Ketchups & Spreads','store_category_description' =>'Ketchup is a sauce used as a condiment. Originally, recipes used egg whites, mushrooms, oysters, mussels, or walnuts, among other ingredients,but now the unmodified term usually refers to tomato ketchup. Various other terms for the sauce include catsup, c','store_category_status' => 1,'company_id' => $company],
['store_id' => $Southeastern_Grocers,'store_category_name' =>'Masalas & Spices','store_category_description' =>'masala is a spice mixture that has been ground into a powder or paste used for cooking Indian food, or a dish flavored with this powder.','store_category_status' => 1,'company_id' => $company],
['store_id' => $Southeastern_Grocers,'store_category_name' =>'Dry Fruits, Nuts & Seeds','store_category_description' =>'Dried fruit is fruit from which the majority of the original water content has been removed either naturally, through sun drying, or through the use of specialized dryers or dehydrators. A nut is a fruit composed of an inedible hard shell and aseed, which','store_category_status' => 1,'company_id' => $company],
['store_id' => $Southeastern_Grocers,'store_category_name' =>'Health Drink Mix','store_category_description' =>'water topped the list, but that doesn\'t mean it\'s the only healthy choice. Unsweetened tea, coffee and milk have a place in your healthy eating plan, while juice, alcohol and sweetened beverages should be consumed sparingly. Water helps restore fluid loss','store_category_status' => 1,'company_id' => $company],
['store_id' => $KFC_Demo,'store_category_name' =>'Snacks','store_category_description' =>'sszcxsds','store_category_status' => 1,'company_id' => $company],
        ]);
        
        Schema::connection('order')->enableForeignKeyConstraints();
    }
}
