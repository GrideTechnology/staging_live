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

$router->post('dial', 'V1\Common\VideoRoomsController@dial_number');
$router->post('/contact', 'V1\Common\CommonController@contact');
$router->post('/login', 'V1\Common\User\UserAuthController@login');
$router->post('/verify', 'V1\Common\User\UserAuthController@verify');
$router->post('/send-otp', 'V1\Common\CommonController@sendOtp');
$router->post('/verify-otp', 'V1\Common\CommonController@verifyOtp');
$router->get('/setdefault/{id}/{query}', 'V1\Common\User\UserAuthController@setdefault');
$router->post('/signup', 'V1\Common\User\UserAuthController@signup');

$router->post('/refresh', 'V1\Common\User\UserAuthController@refresh');
$router->post('/forgot/otp', 'V1\Common\User\UserAuthController@forgotPasswordOTP');
$router->post('/reset/otp', 'V1\Common\User\UserAuthController@resetPasswordOTP');
$router->get('/logout', 'V1\Common\User\UserAuthController@logout');
$router->post('countries', 'V1\Common\User\HomeController@countries');
$router->post('/socket', 'V1\Common\User\SocketController@checkDomain');

// $router->get('/stable', 'V1\Common\User\HomeController@stable');

$router->post('/owner', 'V1\Common\User\OwnerAuthController@login');

$router->group(['middleware' => 'authless:user'], function($app) {

    $app->get('/country/{id}', 'V1\Common\User\HomeController@country');
    $app->get('cities', 'V1\Common\User\HomeController@cities');
    $app->get('/states{$id}', 'V1\Common\CommonController@states_list');
    $app->get('citybystate/{id}', 'V1\Common\CommonController@cities_list');
    $app->post('search-cities', 'V1\Common\User\HomeController@searchcities'); 
    $app->get('promocodes', 'V1\Common\User\HomeController@promocode');
    $app->get('/menus', 'V1\Common\User\HomeController@index');
    $app->post('/call', 'V1\Common\User\HomeController@createCall');

    $app->post('/city', 'V1\Common\User\HomeController@city');
    $app->get('/promocode/{service}', 'V1\Common\User\HomeController@listpromocode');

    
    // $app->post('/create-payment', 'PaymentController@createPayment');
    // $app->get('/handle-payment-response', 'PaymentController@handlePaymentResponse');

    // $app->get('create-transaction', 'PayPalController@createTransaction');
    // $app->get('process-transaction', 'PayPalController@processTransaction');
    // $app->get('success-transaction', 'PayPalController@successTransaction');
    // $app->get('cancel-transaction', 'PayPalController@cancelTransaction');

    $app->post('/paypal/pay', 'PayPalController@pay');
    $app->get('/paypal/return', 'PayPalController@return');



});

$router->group(['middleware' => 'auth:user'], function($app) {
    
    $app->get('/reasons', 'V1\Common\User\HomeController@reasons');

    $app->get('/ongoing', 'V1\Common\User\HomeController@ongoing_services');

	$app->get('/users', function() {
        return response()->json([
            'message' => \Auth::guard('user')->user(),
        ]);
    });

    $app->post('/logout', 'V1\Common\User\UserAuthController@logout');
    $app->get('/profile/delete', 'V1\Common\User\UserAuthController@deleteAccount');
    $app->get('/chat', 'V1\Common\User\HomeController@get_chat');

    $app->post('/address/add', 'V1\Common\User\HomeController@addmanageaddress');
    $app->patch('/address/update', 'V1\Common\User\HomeController@updatemanageaddress');
    $app->get('/address', 'V1\Common\User\HomeController@listmanageaddress');
    $app->delete('/address/{id}', 'V1\Common\User\HomeController@deletemanageaddress');

	$app->get('/profile', 'V1\Common\User\HomeController@show_profile');
    $app->post('/profile', 'V1\Common\User\HomeController@update_profile');
    $app->get('/profileVerify', 'V1\Common\User\HomeController@email_verify');

    $app->post('password', 'V1\Common\User\HomeController@password_update');
    $app->post('card', 'V1\Common\User\HomeController@addcard');
    $app->get('card', 'V1\Common\User\HomeController@carddetail');
    $app->get('walletlist', 'V1\Common\User\HomeController@userlist');
    $app->delete('card/{id}', 'V1\Common\User\HomeController@deleteCard');
    $app->post('/add/money', 'V1\Common\PaymentController@add_money');
    $app->get('/payment/response', 'V1\Common\User\PaymentController@response');
    $app->get('/payment/failure', 'V1\Common\User\PaymentController@failure');
    $app->get('/wallet', 'V1\Common\User\HomeController@walletlist');
    $app->get('/orderstatus', 'V1\Common\User\HomeController@order_status');
    $app->post('/updatelanguage', 'V1\Common\User\HomeController@updatelanguage');
    $app->get('/service/{id}', 'V1\Common\User\HomeController@service');
    $app->get('/service_city_price/{id}', 'V1\Common\User\HomeController@service_city_price');
    $app->get('/notification', 'V1\Common\User\HomeController@notification');
    $app->post('/defaultcard', 'V1\Common\User\HomeController@defaultcard');
    $app->post('device_token', 'V1\Common\User\HomeController@updateDeviceToken');
    $app->post('/wallet/transfer', 'V1\Common\User\HomeController@wallet_transfer');
    $app->post('/initiate-payment', 'V1\Common\PaymentController@getPaymentIntent');

    $app->post('/uploadVideo', 'V1\Common\User\HomeController@uploadVideo');

    $app->get('/call/token', 'V1\Common\VideoRoomsController@voiceaccesstoken');
    $app->get('/call/make', 'V1\Common\VideoRoomsController@CreateCall');

    $app->get('/switchtoowner', 'V1\Common\User\UserAuthController@switchtoowner');

});

$router->post('/account/kit', 'V1\Common\User\SocialLoginController@account_kit');

$router->group(['middleware' => 'auth:owner'], function($app) {
    $app->get('/owner/profile', 'V1\Common\Owner\HomeController@show_profile');
    $app->post('/owner/profile', 'V1\Common\Owner\HomeController@update_profile');
    $app->post('/owner/ach', 'V1\Common\Owner\HomeController@ach_update');
    $app->post('/owner/password', 'V1\Common\Owner\HomeController@password_update');
    // $app->get('/profileVerify', 'V1\Common\User\HomeController@email_verify');

});