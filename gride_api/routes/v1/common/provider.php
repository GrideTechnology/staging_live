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

$router->post('/login', 'V1\Common\Provider\ProviderAuthController@login');
$router->post('/verify', 'V1\Common\Provider\ProviderAuthController@verify');
$router->post('/send-otp', 'V1\Common\CommonController@sendOtp');
$router->post('/verify-otp', 'V1\Common\CommonController@verifyOtp');

$router->post('/signup', 'V1\Common\Provider\ProviderAuthController@signup');
$router->post('/subscription/payment_initiate', 'V1\Common\Provider\ProviderAuthController@SubscriptionPaymentSession');

$router->post('/refresh', 'V1\Common\Provider\ProviderAuthController@refresh');
$router->post('/forgot/otp', 'V1\Common\Provider\ProviderAuthController@forgotPasswordOTP');
$router->post('/reset/otp', 'V1\Common\Provider\ProviderAuthController@resetPasswordOTP');

$router->get('/notify', 'V1\Common\Provider\HomeController@Notify');

$router->post('countries', 'V1\Common\Provider\HomeController@countries');

$router->get('subscriptions', 'V1\Common\Provider\HomeController@subscriptions');
$router->get('subscriptions/{id}', 'V1\Common\Provider\HomeController@subscription');

$router->post('cities/{id}', 'V1\Common\Provider\HomeController@cities');

$router->post('/updatelocation', 'V1\Common\Provider\HomeController@updatelocation');

$router->group(['middleware' => 'auth:provider'], function($app) {

    $app->post('/logout', 'V1\Common\Provider\ProviderAuthController@logout');

    $app->get('/chat', 'V1\Common\Provider\HomeController@get_chat');
    $app->post('/call', 'V1\Common\Provider\HomeController@createCall');

    $app->get('/check/request', 'V1\Common\Provider\HomeController@index');

    $app->post('/accept/request', 'V1\Common\Provider\HomeController@accept_request');  

    // $app->get('/check/serve/request', 'V1\Service\Provider\ServeController@index');

    $app->post('/cancel/request', 'V1\Common\Provider\HomeController@cancel_request');

    $app->post('/listdocuments', 'V1\Common\Provider\ProviderAuthController@listdocuments');
    $app->get('/carimages/{id}', 'V1\Common\Provider\ProviderAuthController@listCarImages');

    $app->post('/documents', 'V1\Common\Provider\ProviderAuthController@document_store');

    $app->get('companydocument/{id}', 'V1\Common\Provider\HomeController@companydocument');
    $app->get('provider-document-consent/{id}', 'V1\Common\Provider\ProviderAuthController@providerConsentDocument');
    $app->post('update-document-consent/{id}', 'V1\Common\Provider\ProviderAuthController@updateConsentDocument');
    
    $app->get('/profile', 'V1\Common\Provider\HomeController@show_profile');
    $app->post('/profile', 'V1\Common\Provider\HomeController@update_profile');
    $app->post('/password', 'V1\Common\Provider\HomeController@password_update');

    $app->get('/reviews/','V1\Common\Provider\HomeController@reviews');

    $app->post('/card', 'V1\Common\Provider\HomeController@addcard');
    $app->get('card', 'V1\Common\Provider\HomeController@carddetail');
    $app->get('list', 'V1\Common\Provider\HomeController@providerlist');
    $app->delete('card/{id}', 'V1\Common\Provider\HomeController@deleteCard');
    $app->post('/add/money', 'V1\Common\PaymentController@add_money');
    $app->get('/payment/response', 'V1\Common\Provider\PaymentController@response');
    $app->get('/payment/failure', 'V1\Common\Provider\PaymentController@failure');
    $app->get('/wallet', 'V1\Common\Provider\HomeController@walletlist');
    $app->get('services/list', 'V1\Common\Provider\HomeController@provider_services');
    $app->get('/braintree/token', 'V1\Common\Provider\HomeController@getClientToken');
    $app->post('/initiate-payment', 'V1\Common\PaymentController@getPaymentIntent');
    
    $app->post('/vehicle', 'V1\Common\Provider\HomeController@add_vehicle');
    $app->delete('providerdocument/{id}', 'V1\Common\Provider\HomeController@deleteproviderdocument');
    $app->post('/service', 'V1\Common\Provider\HomeController@add_service');
    $app->get('/vehicle', 'V1\Common\Provider\HomeController@vehicle_list');
    $app->get('/orderstatus', 'V1\Common\Provider\HomeController@order_status');
    $app->post('/vechile/add', 'V1\Common\Provider\HomeController@addvechile');
    $app->post('/vechile/multipleadd', 'V1\Common\Provider\HomeController@multipleaddvehicle');
    $app->post('/vechile/addservice', 'V1\Common\Provider\HomeController@addproviderservice');
    $app->post('/vechile/editservice', 'V1\Common\Provider\HomeController@editproviderservice');
   // $app->post('/vehicle/edit', 'V1\Common\Provider\HomeController@editvechile');
    $app->post('/vechile/multipleedit', 'V1\Common\Provider\HomeController@multipleeditvehicle');

    $app->post('/vehicle/add', 'V1\Common\Provider\HomeController@addvehicle');
    $app->post('/vehicle/edit', 'V1\Common\Provider\HomeController@editvehicle');
    $app->post('/vehicle/delete', 'V1\Common\Provider\HomeController@deletevehicle');
    
    $app->get('/reasons', 'V1\Common\Provider\HomeController@reasons');
    $app->post('/updatelanguage', 'V1\Common\Provider\HomeController@updatelanguage');
    $app->get('/adminservices', 'V1\Common\Provider\HomeController@adminservices');
    $app->get('/notification', 'V1\Common\Provider\HomeController@notification');
    $app->get('/bankdetails/template', 'V1\Common\Provider\HomeController@template');
    $app->post('/addbankdetails', 'V1\Common\Provider\HomeController@addbankdetails');
    $app->post('/editbankdetails', 'V1\Common\Provider\HomeController@editbankdetails');
    $app->post('/referemail', 'V1\Common\Provider\HomeController@refer_email');
    $app->post('/defaultcard', 'V1\Common\Provider\HomeController@defaultcard');
    $app->get('/onlinestatus/{id}', 'V1\Common\Provider\HomeController@onlinestatus');
    $app->get('/earnings/{id}', 'V1\Common\Provider\HomeController@totalEarnings');
    $app->post('/wallet/transfer', 'V1\Common\Provider\HomeController@wallet_transfer');
    $app->get('/providers', function() {
        return response()->json([
            'message' => \Auth::guard('provider')->user(), 
        ]);
    });
	$app->post('device_token', 'V1\Common\Provider\HomeController@updateDeviceToken');
    $app->post('/uploadVideo', 'V1\Common\Provider\HomeController@uploadVideo');

    $app->get('/call/token', 'V1\Common\VideoRoomsController@voiceaccesstoken');
});

$router->post('/clear', 'V1\Common\Provider\HomeController@clear');