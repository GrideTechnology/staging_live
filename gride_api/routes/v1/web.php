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

$router->get('/', function () use ($router) {
   return view('index');
});

$router->post('verify', 'LicenseController@verify');


$router->post('base', 'V1\Common\CommonController@base');

$router->get('delete-log','V1\Common\CommonController@deleteLog');
$router->get('cmspage/{type}', 'V1\Common\CommonController@cmspagetype');
$router->post('blogs', 'V1\Common\CommonController@blogList');
$router->get('single-blog/{id}', 'V1\Common\CommonController@singleBlog');

$router->group(['prefix' => 'api/v1'], function ($app) {
    $app->post('newbase', 'V1\Common\CommonController@base');

    $app->post('restaurantsignup', 'V1\Common\CommonController@RestaurantSignup');

	$app->post('user/appsettings', 'V1\Common\CommonController@base');

	$app->post('provider/appsettings', 'V1\Common\CommonController@base');

	$app->get('countries', 'V1\Common\CommonController@countries_list');

	$app->get('user/states/{id}', 'V1\Common\CommonController@states_list');

	$app->get('cities/{id}', 'V1\Common\CommonController@cities_list');

	$app->post('/{provider}/social/login', 'V1\Common\SocialLoginController@handleSocialLogin');

	$app->post('/chat', 'V1\Common\CommonController@chat');

	$app->post('/goonline', 'V1\Common\CommonController@goonline');

	$app->post('/provider/update/location', 'V1\Common\Provider\HomeController@update_location');

	$app->get('appsetting', 'V1\Common\CommonController@appsetting');

});

$router->get('/send/{type}/push', 'V1\Common\SocialLoginController@push');

//Send Mail
$router->post('send/mail', 'V1\Common\CommonController@sendmail');
$router->get('getfcmtoken', 'V1\Common\CommonController@getFcmToken');

$router->get('v1/docs', ['as' => 'swagger-v1-lume.docs', 'middleware' => config('swagger-lume.routes.middleware.docs', []), 'uses' => 'V1\Common\SwaggerController@docs']);

$router->get('/api/v1/documentation', ['as' => 'swagger-v1-lume.api', 'middleware' => config('swagger-lume.routes.middleware.api', []), 'uses' => 'V1\Common\SwaggerController@api']);
