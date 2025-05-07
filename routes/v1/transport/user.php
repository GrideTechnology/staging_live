<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['middleware' => 'authless:user'], function($app) {

    $app->get('/transport/services', 'V1\Transport\User\RideController@services');

	$app->post('/transport/estimate', 'V1\Transport\User\RideController@estimate');
	$app->get('/transport/cars-list/{id}', 'V1\Transport\User\RideController@carList');
	$app->get('/transport/cars-type-list', 'V1\Transport\User\RideController@carTypeList');
	$app->get('/transport/cars-details/{id}', 'V1\Transport\User\RideController@carDetails');
	$app->post('/transport/recommended-cars', 'V1\Transport\User\RideController@recommendedCars');
	$app->get('/transport/carbooking-status/{id}', 'V1\Transport\User\RideController@carstatus');
	$app->post('/transport/cars-filter/{id}', 'V1\Transport\User\RideController@carListFiltered');
	$app->post('/transport/car-availability/{id}', 'V1\Transport\User\RentalController@carAvailability');
	$app->get('/transport/admincharges', 'V1\Transport\User\RideController@adminCharges');
	$app->post('/transport/vin-details', 'V1\Transport\User\RideController@getDetailByVin');
	$app->post('/transport/estimateadmin', 'V1\Transport\User\RideController@estimateadmin');
});


$router->get('/transport/vehicle-list', 'V1\Transport\User\RideController@vehicleList');
$router->get('/transport/vehicle-list/{id}/cars', 'V1\Transport\User\RideController@carList');

$router->group(['middleware' => 'auth:user'], function($app) {

	// $app->get('/transport/services', 'V1\Transport\User\RideController@services');

	$app->post('/transport/send/request', 'V1\Transport\User\RideController@create_ride');

	$app->get('/transport/check/request', 'V1\Transport\User\RideController@status');

	$app->get('/transport/request/{id}', 'V1\Transport\User\RideController@checkRide');

	$app->post('/transport/cancel/request', 'V1\Transport\User\RideController@cancel_ride');

	$app->post('/transport/extend/trip', 'V1\Transport\User\RideController@extend_trip');

	$app->post('/transport/rate', 'V1\Transport\User\RideController@rate'); 

    $app->post('/transport/payment', 'V1\Transport\User\RideController@payment');

    $app->post('/transport/update/payment', 'V1\Transport\User\RideController@update_payment_method');
    

    // $app->get('/trips', 'V1\Transport\User\HomeController@trips');
	// $app->get('/trips/{id}', 'V1\Transport\User\HomeController@gettripdetails');
	$app->get('/trips-history/transport', 'V1\Transport\User\HomeController@trips');
	$app->get('/trips-history/transport/{id}', 'V1\Transport\User\HomeController@gettripdetails');
	$app->get('/trips-history/transport/invoice/{id}', 'V1\Transport\User\RideController@invoicePDF');
	// $app->get('/upcoming/trips/transport', 'V1\Transport\User\HomeController@upcoming_trips');
	$app->get('/upcoming/trips/transport/{id}', 'V1\Transport\User\HomeController@getupcomingtrips');
	// $app->get('/upcoming/trips', 'V1\Transport\User\HomeController@upcoming_trips');
	// $app->get('/upcoming/trips/{id}', 'V1\Transport\User\HomeController@getupcomingtrips');
	$app->post('/ride/dispute', 'V1\Transport\User\HomeController@ride_request_dispute');
	$app->post('/ride/lostitem', 'V1\Transport\User\HomeController@ride_lost_item');
	$app->get('/ride/dispute', 'V1\Transport\User\HomeController@getUserdisputedetails');
	$app->get('/ride/disputestatus/{id}', 'V1\Transport\User\HomeController@get_ride_request_dispute');
	$app->get('/ride/lostitem/{id}', 'V1\Transport\User\HomeController@get_ride_lost_item');

//User rental API
	$app->get('/transport/dashboard', 'V1\Transport\User\RentalController@dashboard');
	$app->get('/transport/bookings', 'V1\Transport\User\RentalController@bookedcar');
	$app->post('/transport/{id}/bookcar', 'V1\Transport\User\RentalController@bookcar');
	$app->get('/transport/user-inbox', 'V1\Transport\User\ChatController@userchatinbox');
	$app->post('/transport/user-messages', 'V1\Transport\User\ChatController@ownermessages');
	$app->post('/transport/send-user-message', 'V1\Transport\User\ChatController@sendUserMessage');
	$app->get('/transport/chat-notification', 'V1\Transport\User\ChatController@getnotification');
	$app->get('/transport/booking-details/{id}', 'V1\Transport\User\RentalController@rentalDetails');



	
});

$router->group(['middleware' => 'auth:owner'], function($app) {
	//Owner rental API
	$app->get('/transport/owner-dashboard', 'V1\Transport\User\RideController@dashboard');
	$app->get('/transport/documentstatus', 'V1\Transport\User\RideController@documentStatus');
	$app->get('/transport/owner-dashboard', 'V1\Transport\User\RideController@dashboard');
	$app->get('/transport/pickups', 'V1\Transport\User\RideController@pickups');
	$app->get('/transport/owner-cars', 'V1\Transport\User\RideController@ownercars');
	$app->get('/transport/owner-bookings', 'V1\Transport\User\RideController@ownerbookings');
	$app->get('/transport/payment-list', 'V1\Transport\User\RideController@paymentlist');
	$app->get('/transport/inbox', 'V1\Transport\User\ChatController@ownerchatinbox');
	$app->post('/transport/messages', 'V1\Transport\User\ChatController@usermessages');
	$app->post('/transport/owner/send-message', 'V1\Transport\User\ChatController@sendOwnerMessage');
	$app->get('/transport/owner/chat-notification', 'V1\Transport\User\ChatController@getOwnernotification');
	$app->post('/transport/addcar', 'V1\Transport\User\RideController@addcar');
	$app->post('/transport/updatecar', 'V1\Transport\User\RideController@updateCar');

});