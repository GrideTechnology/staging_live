<?php

Route::get('/login', function () {

    $base_url = \App\Helpers\Helper::getBaseUrl();

    $services = json_decode(\App\Helpers\Helper::getServiceBaseUrl(), true);

    $settings = json_encode(\App\Helpers\Helper::getSettings());

    $base = [];

    foreach ($services as $key => $service) {
        $base[$key] = $service;
    }

    $base = json_encode($base); 

    return view('transport.owner.auth.login', compact('base', 'base_url', 'settings'));
});

//Route::view('/dashboard', 'transport.owner.ride.listing-dashboard');
Route::view('/listing', 'transport.owner.ride.listing');
Route::view('/finance', 'transport.owner.ride.listing-finance');
Route::view('/add-listing', 'transport.owner.ride.add-listing');
Route::view('/rentals', 'transport.owner.ride.listing-rentals');

Route::view('/messages', 'transport.owner.ride.inbox');

Route::get('/edit-listing/{id}', function ($id) {
   
    return view('transport.owner.ride.edit-listing',compact('id'));
});

Route::get('/forgot-password', function () {
    $base_url = \App\Helpers\Helper::getBaseUrl();
    $services = json_decode(\App\Helpers\Helper::getServiceBaseUrl(), true);
    $settings = json_encode(\App\Helpers\Helper::getSettings());
    $base = [];
    foreach ($services as $key => $service) {
        $base[$key] = $service;
    }
    $base = json_encode($base); 
    return view('transport.owner.auth.forgot', compact('base', 'base_url', 'settings'));
});
Route::get('/reset-password', function () {
    $urlparam = ($_GET);
    $base_url = \App\Helpers\Helper::getBaseUrl();
    $services = json_decode(\App\Helpers\Helper::getServiceBaseUrl(), true);
    $settings = json_encode(\App\Helpers\Helper::getSettings());
    $base = [];
    foreach ($services as $key => $service) {
        $base[$key] = $service;
    }
    $base = json_encode($base); 
    return view('transport.owner.auth.reset', compact('base', 'base_url', 'settings','urlparam'));
});


Route::get('/profile/{type}', function ($type) {
   
    return view('common.user.account.owner-profile',compact('type'));
});



Route::get('/dashboard', function () {

    $base_url = \App\Helpers\Helper::getBaseUrl();

    $services = json_decode(\App\Helpers\Helper::getServiceBaseUrl(), true);

    $settings = json_encode(\App\Helpers\Helper::getSettings());

    $base = [];

    foreach ($services as $key => $service) {
        $base[$key] = $service;
    }

    $base = json_encode($base); 

    return view('transport.owner.ride.listing-dashboard', compact('base', 'base_url', 'settings'));
});

Route::redirect('/', '/owner/login');

Route::get('/logout', function () {
    return view('transport.owner.auth.logout');
});
