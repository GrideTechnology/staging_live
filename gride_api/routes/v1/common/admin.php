<?php

$router->post('/login', 'V1\Common\Admin\Auth\AdminAuthController@login');

$router->post('/refresh', 'V1\Common\Admin\Auth\AdminAuthController@refresh');

$router->post('/forgotOtp', 'V1\Common\Admin\Auth\AdminAuthController@forgotPasswordOTP');
$router->post('/resetOtp', 'V1\Common\Admin\Auth\AdminAuthController@resetPasswordOTP');

$router->post('/cmspage/upload', 'V1\Common\Admin\Resource\CmsController@upload');

$router->group(['middleware' => 'auth:admin'], function ($app) {

    $app->post('/push-subscription','V1\Common\Admin\Auth\AdminController@push_subscription');

    $app->post('/permission_list', 'V1\Common\Admin\Auth\AdminAuthController@permission_list');    

    $app->get('/contact-query', ['uses' => 'V1\Common\Admin\Resource\UserController@contactQuery']);

    $app->get('/users', ['uses' => 'V1\Common\Admin\Resource\UserController@index', 'middleware' => ['permission:user-list']]);

    $app->post('/users', ['uses' => 'V1\Common\Admin\Resource\UserController@store', 'middleware' => ['permission:user-create']]);

    $app->get('/users/{id}', ['uses' => 'V1\Common\Admin\Resource\UserController@show', 'middleware' => ['permission:user-list']]);

    $app->patch('/users/{id}', ['uses' => 'V1\Common\Admin\Resource\UserController@update', 'middleware' => ['permission:user-edit', 'demo']]);

    $app->delete('/users/{id}', ['uses' => 'V1\Common\Admin\Resource\UserController@destroy', 'middleware' => ['permission:user-delete', 'demo']]);

    $app->post('/backup', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Auth\AdminController@backup']);

    $app->get('/users/{id}/updateStatus', ['uses' => 'V1\Common\Admin\Resource\UserController@updateStatus', 'middleware' => ['permission:user-edit']]);

    $app->post('/user_status_check', 'V1\Common\Admin\Resource\UserController@user_status_check'); 
    
    $app->get('/blog', ['uses' => 'V1\Common\Admin\Resource\BlogController@index', 'middleware' => ['permission:blog-list']]);
    $app->delete('/blog/{id}', ['uses' => 'V1\Common\Admin\Resource\BlogController@delete', 'middleware' => ['permission:blog-delete']]);
    $app->post('/blog',['uses' => 'V1\Common\Admin\Resource\BlogController@store', 'middleware' => ['permission:blog-create']]);
    $app->patch('/blog/{id}', ['uses' => 'V1\Common\Admin\Resource\BlogController@update', 'middleware' => ['permission:blog-edit']]);
    $app->get('/blog/{id}', ['uses' => 'V1\Common\Admin\Resource\BlogController@show', 'middleware' => ['permission:blog-history']]);

    $app->get('/{type}/logs/{id}', 'V1\Common\CommonController@logdata');

    $app->get('/{type}/wallet/{id}', 'V1\Common\CommonController@walletDetails');

    $app->post('/logout', 'V1\Common\Admin\Auth\AdminAuthController@logout');

    $app->get('/services/main/list', 'V1\Common\CommonController@admin_services');

    $app->get('/services/list/{id}', 'V1\Common\Admin\Resource\ProviderController@provider_services');


    //Document
    $app->get('/document', ['uses' => 'V1\Common\Admin\Resource\DocumentController@index', 'middleware' => ['permission:documents-list']]);

    $app->post('/document', ['uses' => 'V1\Common\Admin\Resource\DocumentController@store', 'middleware' => ['permission:documents-create']]);

    $app->get('/document/{id}', ['uses' => 'V1\Common\Admin\Resource\DocumentController@show', 'middleware' => ['permission:documents-list']]);

    $app->patch('/document/{id}', ['uses' => 'V1\Common\Admin\Resource\DocumentController@update', 'middleware' => ['permission:documents-edit', 'demo']]);

    $app->delete('/document/{id}', ['uses' => 'V1\Common\Admin\Resource\DocumentController@destroy', 'middleware' => ['permission:documents-delete', 'demo']]);

    $app->get('/document/{id}/updateStatus', 'V1\Common\Admin\Resource\DocumentController@updateStatus');

    //Company Document
    $app->get('/company-document', ['uses' => 'V1\Common\Admin\Resource\CompanyDocumentController@index', 'middleware' => ['permission:documents-list']]);

    $app->post('/company-document', ['uses' => 'V1\Common\Admin\Resource\CompanyDocumentController@store', 'middleware' => ['permission:documents-create']]);

    $app->get('/company-document/{id}', ['uses' => 'V1\Common\Admin\Resource\CompanyDocumentController@show', 'middleware' => ['permission:documents-list']]);

    $app->patch('/company-document/{id}', ['uses' => 'V1\Common\Admin\Resource\CompanyDocumentController@update', 'middleware' => ['permission:documents-edit', 'demo']]);

    $app->delete('/company-document/{id}', ['uses' => 'V1\Common\Admin\Resource\CompanyDocumentController@destroy', 'middleware' => ['permission:documents-delete', 'demo']]);

    $app->get('/company-document/{id}/updateStatus', 'V1\Common\Admin\Resource\CompanyDocumentController@updateStatus');


    //Notification
    $app->get('/notification', ['uses' => 'V1\Common\Admin\Resource\NotificationController@index', 'middleware' => ['permission:notification-list']]);

    $app->post('/notification', ['uses' => 'V1\Common\Admin\Resource\NotificationController@store', 'middleware' => ['permission:notification-create']]);

    $app->get('/notification/{id}', ['uses' => 'V1\Common\Admin\Resource\NotificationController@show', 'middleware' => ['permission:notification-list']]);

    $app->patch('/notification/{id}', ['uses' => 'V1\Common\Admin\Resource\NotificationController@update', 'middleware' => ['permission:notification-edit', 'demo']]);

    $app->delete('/notification/{id}', ['uses' => 'V1\Common\Admin\Resource\NotificationController@destroy', 'middleware' => ['permission:notification-delete', 'demo']]);


    //Reason
    $app->get('/reason', ['uses' => 'V1\Common\Admin\Resource\ReasonController@index', 'middleware' => ['permission:cancel-reasons-list']]);

    $app->post('/reason', ['uses' => 'V1\Common\Admin\Resource\ReasonController@store', 'middleware' => ['permission:cancel-reasons-create']]);

    $app->get('/reason/{id}', ['uses' => 'V1\Common\Admin\Resource\ReasonController@show', 'middleware' => ['permission:cancel-reasons-list']]);

    $app->patch('/reason/{id}', ['uses' => 'V1\Common\Admin\Resource\ReasonController@update', 'middleware' => ['permission:cancel-reasons-edit', 'demo']]);

    $app->delete('/reason/{id}', ['uses' => 'V1\Common\Admin\Resource\ReasonController@destroy', 'middleware' => ['permission:cancel-reasons-delete', 'demo']]);

    //Fleet
    $app->get('/fleet', ['uses' => 'V1\Common\Admin\Resource\FleetController@index', 'middleware' => ['permission:fleet-list']]);

    $app->post('/fleet', ['uses' => 'V1\Common\Admin\Resource\FleetController@store', 'middleware' => ['permission:fleet-create']]);

    $app->get('/fleet/{id}', ['uses' => 'V1\Common\Admin\Resource\FleetController@show', 'middleware' => ['permission:fleet-list']]);

    $app->patch('/fleet/{id}', ['uses' => 'V1\Common\Admin\Resource\FleetController@update', 'middleware' => ['permission:fleet-edit', 'demo']]);

    $app->delete('/fleet/{id}', ['uses' => 'V1\Common\Admin\Resource\FleetController@destroy', 'middleware' => ['permission:fleet-delete', 'demo']]);

    $app->get('/fleet/{id}/updateStatus', ['uses' => 'V1\Common\Admin\Resource\FleetController@show', 'middleware' => ['permission:fleet-list']]);


    $app->post('/card', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\FleetController@addcard']);

    $app->get('card', 'V1\Common\Admin\Resource\FleetController@card');

    $app->post('add/money', 'V1\Common\Admin\Resource\FleetController@wallet');
    // $app->get('wallet', 'V1\Common\Admin\Resource\FleetController@wallet');
    $app->get('adminfleet/wallet', 'V1\Common\Admin\Resource\FleetController@wallet');

    //Dispatcher Panel
    $app->get('/dispatcher/trips', 'V1\Common\Admin\Resource\DispatcherController@trips');

    $app->get('/list', 'V1\Common\Admin\Resource\DispatcherController@providerServiceList');

    //Dispatcher
    $app->get('/dispatcher', ['uses' => 'V1\Common\Admin\Resource\DispatcherController@index', 'middleware' => ['permission:dispatcher-list']]);

    $app->post('/dispatcher', ['uses' => 'V1\Common\Admin\Resource\DispatcherController@store', 'middleware' => ['permission:dispatcher-create']]);

    $app->get('/dispatcher/{id}', ['uses' => 'V1\Common\Admin\Resource\DispatcherController@show', 'middleware' => ['permission:dispatcher-list']]);

    $app->patch('/dispatcher/{id}', ['uses' => 'V1\Common\Admin\Resource\DispatcherController@update', 'middleware' => ['permission:dispatcher-edit', 'demo']]);

    $app->delete('/dispatcher/{id}', ['uses' => 'V1\Common\Admin\Resource\DispatcherController@destroy', 'middleware' => ['permission:dispatcher-delete', 'demo']]);

    $app->get('/dispatcher/get/providers', 'V1\Common\Admin\Resource\DispatcherController@providers');

    $app->post('/dispatcher/assign', 'V1\Common\Admin\Resource\DispatcherController@assign');

    $app->post('/dispatcher/ride/request', 'V1\Common\Admin\Resource\DispatcherController@create_ride');

    $app->post('/dispatcher/ride/cancel', 'V1\Common\Admin\Resource\DispatcherController@cancel_ride');

    $app->post('/dispatcher/service/request', 'V1\Common\Admin\Resource\DispatcherController@create_service');

    $app->post('/dispatcher/service/cancel', 'V1\Common\Admin\Resource\DispatcherController@cancel_service');

    $app->get('/fare' , 'V1\Common\Admin\Resource\DispatcherController@fare');

    //Account Manager
    $app->get('/accountmanager', ['uses' => 'V1\Common\Admin\Resource\AccountManagerController@index', 'middleware' => ['permission:account-manager-list']]);

    $app->post('/accountmanager', ['uses' => 'V1\Common\Admin\Resource\AccountManagerController@store', 'middleware' => ['permission:account-manager-create']]);

    $app->get('/accountmanager/{id}', ['uses' => 'V1\Common\Admin\Resource\AccountManagerController@show', 'middleware' => ['permission:account-manager-list']]);

    $app->patch('/accountmanager/{id}', ['uses' => 'V1\Common\Admin\Resource\AccountManagerController@update', 'middleware' => ['permission:account-manager-edit', 'demo']]);

    $app->delete('/accountmanager/{id}', ['uses' => 'V1\Common\Admin\Resource\AccountManagerController@destroy', 'middleware' => ['permission:account-manager-delete', 'demo']]);
    

    //Promocodes
    $app->get('/promocode', ['uses' => 'V1\Common\Admin\Resource\PromocodeController@index', 'middleware' => ['permission:promocodes-list']]);

    $app->post('/promocode', ['uses' => 'V1\Common\Admin\Resource\PromocodeController@store', 'middleware' => ['permission:promocodes-create']]);

    $app->get('/promocode/{id}', ['uses' => 'V1\Common\Admin\Resource\PromocodeController@show', 'middleware' => ['permission:promocodes-list']]);

    $app->patch('/promocode/{id}', ['uses' => 'V1\Common\Admin\Resource\PromocodeController@update', 'middleware' => ['permission:promocodes-edit', 'demo']]);

    $app->delete('/promocode/{id}', ['uses' => 'V1\Common\Admin\Resource\PromocodeController@destroy', 'middleware' => ['permission:promocodes-delete', 'demo']]);

    //Geofencing
    $app->get('/geofence', ['uses' => 'V1\Common\Admin\Resource\GeofenceController@index', 'middleware' => ['permission:geofence-list']]);

    $app->post('/geofence', ['uses' => 'V1\Common\Admin\Resource\GeofenceController@store', 'middleware' => ['permission:geofence-create']]);

    $app->get('/geofence/{id}', ['uses' => 'V1\Common\Admin\Resource\GeofenceController@show', 'middleware' => ['permission:geofence-list']]);

    $app->patch('/geofence/{id}', ['uses' => 'V1\Common\Admin\Resource\GeofenceController@update', 'middleware' => ['permission:geofence-edit', 'demo']]);

    $app->get('/geofence/{id}/updateStatus', ['uses' => 'V1\Common\Admin\Resource\GeofenceController@updateStatus', 'middleware' => ['permission:geofence-list']]);

    $app->delete('/geofence/{id}', ['uses' => 'V1\Common\Admin\Resource\GeofenceController@destroy', 'middleware' => ['permission:geofence-delete', 'demo']]);

    //Dispute
    $app->get('/dispute_list', ['uses' => 'V1\Common\Admin\Resource\DisputeController@index', 'middleware' => ['permission:dispute-list']]);

    $app->post('/dispute', ['uses' => 'V1\Common\Admin\Resource\DisputeController@store', 'middleware' => ['permission:dispute-create']]);

    $app->get('/dispute/{id}', ['uses' => 'V1\Common\Admin\Resource\DisputeController@show', 'middleware' => ['permission:dispute-list']]);

    $app->patch('/dispute/{id}', ['uses' => 'V1\Common\Admin\Resource\DisputeController@update', 'middleware' => ['permission:dispute-edit', 'demo']]);

    $app->delete('/dispute/{id}', ['uses' => 'V1\Common\Admin\Resource\DisputeController@destroy', 'middleware' => ['permission:dispute-delete', 'demo']]);


    //Provider
    $app->get('/provider', ['uses' => 'V1\Common\Admin\Resource\ProviderController@index', 'middleware' => ['permission:provider-list']]);

    $app->post('/provider', ['uses' => 'V1\Common\Admin\Resource\ProviderController@store', 'middleware' => ['permission:provider-create']]);

    $app->get('/provider/{id}', ['uses' => 'V1\Common\Admin\Resource\ProviderController@show', 'middleware' => ['permission:provider-list']]);

    $app->post('/provider/carimages', ['uses' => 'V1\Common\Admin\Resource\ProviderController@listCarImages', 'middleware' => ['permission:provider-list']]);

    $app->patch('/provider/{id}', ['uses' => 'V1\Common\Admin\Resource\ProviderController@update', 'middleware' => ['permission:provider-edit', 'demo']]);

    $app->delete('/provider/{id}', ['uses' => 'V1\Common\Admin\Resource\ProviderController@destroy', 'middleware' => ['permission:provider-delete', 'demo']]);

    


    $app->get('/provider/{id}/company_document', ['uses' => 'V1\Common\Admin\Resource\ProviderController@view_company_document', 'middleware' => ['permission:provider-list']]);
    $app->get('/provider/{id}/updateStatus', ['uses' => 'V1\Common\Admin\Resource\ProviderController@updateStatus', 'middleware' => ['permission:provider-status']]);
    $app->get('/provider/approve/{id}', ['uses' => 'V1\Common\Admin\Resource\ProviderController@approveStatus', 'middleware' => ['permission:provider-status']]);
    $app->get('/provider/approve/company_document/{id}', ['uses' => 'V1\Common\Admin\Resource\ProviderController@approve_company_document', 'middleware' => ['permission:provider-status']]);
    $app->get('/provider/{id}/company_document', ['uses' => 'V1\Common\Admin\Resource\ProviderController@view_company_document', 'middleware' => ['permission:provider-status']]);

    $app->get('/provider/zoneapprove/{id}', ['uses' => 'V1\Common\Admin\Resource\ProviderController@zoneStatus', 'middleware' => ['permission:provider-status']]);
    $app->post('/provider/addamount/{id}', ['uses' => 'V1\Common\Admin\Resource\ProviderController@addamount', 'middleware' => ['permission:provider-status']]);
    $app->get('/available/provider', ['uses' => 'V1\Transport\User\RideController@providerAvailability']);
    //sub admin

    $app->get('/subadminlist/{type}', 'V1\Common\Admin\Resource\AdminController@index');

    $app->post('/subadmin', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\AdminController@store']);

    $app->get('/subadmin/{id}', 'V1\Common\Admin\Resource\AdminController@show');

    $app->patch('/subadmin/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\AdminController@update']);

    $app->delete('/subadmin/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\AdminController@destroy']);

    $app->get('/subadmin/{id}/updateStatus', 'V1\Common\Admin\Resource\AdminController@updateStatus');


    $app->get('/storelist/{id}', 'V1\Common\Admin\Resource\AdminController@store_list');
    $app->get('/store/details/{id}', 'V1\Common\Admin\Resource\AdminController@store_details');
    $app->get('/store/products/{id}', 'V1\Common\Admin\Resource\AdminController@store_products');
    $app->post('/store/price/estimate', 'V1\Common\Admin\Resource\AdminController@ProductCheckoutCalculate');
    $app->get('/price/service', 'V1\Common\Admin\Resource\AdminController@services');
    $app->post('/price/estimate', 'V1\Common\Admin\Resource\AdminController@estimate');

    
    $app->get('/heatmap', ['uses' => 'V1\Common\Admin\Resource\AdminController@heatmap', 'middleware' => ['permission:heat-map']]);

    $app->get('/role_list', ['uses' => 'V1\Common\Admin\Resource\AdminController@role_list', 'middleware' => ['permission:role-list']]);

    // For restaurant
    $app->get('/RestaurantUserlist', 'V1\Common\Admin\Resource\AdminController@RestaurantUserlist');
    $app->get('/RestaurantUserlist/{id}', 'V1\Common\Admin\Resource\AdminController@RestaurantUserEdit');
    $app->patch('/RestaurantUserlist/{id}', 'V1\Common\Admin\Resource\AdminController@RestaurantUserUpdate');
    $app->delete('/RestaurantUserlist/{id}', 'V1\Common\Admin\Resource\AdminController@RestaurantUserDestroy');

     // For Delivery Charges
     $app->get('/deliverycharges', 'V1\Common\Admin\Resource\AdminController@deliveryCharges');
     $app->get('/deliverycharges/{id}', 'V1\Common\Admin\Resource\AdminController@editdeliveryCharges');
     $app->post('/updatecharge/{id}', 'V1\Common\Admin\Resource\AdminController@updatecharges');

    //Subscription
    $app->get('/subscription', 'V1\Common\Admin\Resource\SubscriptionController@index');

    $app->post('/subscription', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\SubscriptionController@store']);

    $app->get('/subscription/{id}', 'V1\Common\Admin\Resource\SubscriptionController@show');

    $app->patch('/subscription/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\SubscriptionController@update']);

    $app->delete('/subscription/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\SubscriptionController@destroy']);
 
    //cmspages
    $app->get('/cmspage', 'V1\Common\Admin\Resource\CmsController@index');

    $app->post('/cmspage', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CmsController@store']);

    $app->get('/cmspage/{id}', 'V1\Common\Admin\Resource\CmsController@show');

    $app->patch('/cmspage/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CmsController@update']);

    $app->delete('/cmspage/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CmsController@destroy']);

    //custom push
    $app->get('/custompush', 'V1\Common\Admin\Resource\CustomPushController@index');

    $app->post('/custompush', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CustomPushController@store']);

    $app->get('/custompush/{id}', 'V1\Common\Admin\Resource\CustomPushController@show');

    $app->patch('/custompush/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CustomPushController@update']);

    $app->delete('/custompush/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CustomPushController@destroy']);

    //Provider add vehicle
    $app->get('/ProviderService/{id}', 'V1\Common\Admin\Resource\ProviderController@ProviderService');

    $app->patch('/vehicle_type', 'V1\Common\Admin\Resource\ProviderController@vehicle_type');

    $app->get('/service_on/{id}', 'V1\Common\Admin\Resource\ProviderController@service_on');

    $app->get('/service_off/{id}', 'V1\Common\Admin\Resource\ProviderController@service_off');

    $app->get('/deleteservice/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\ProviderController@deleteservice']);
    $app->get('/deletevehicle/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\ProviderController@deletevehicle']);
    $app->get('/lock-unlock-vehicle/{id}/{status}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\ProviderController@lockunlockvehicle']);
    //Provider view document
    $app->get('/provider/{id}/view_document', 'V1\Common\Admin\Resource\ProviderController@view_document');

    $app->get('/provider/approve_image/{id}', 'V1\Common\Admin\Resource\ProviderController@approve_image');

    $app->get('/provider/approveall/{id}', 'V1\Common\Admin\Resource\ProviderController@approve_all');

    $app->delete('/provider/delete_view_image/{id}', 'V1\Common\Admin\Resource\ProviderController@delete_view_image');
    //CompanyCountry
    $app->get('/providerdocument/{id}', 'V1\Common\Admin\Resource\ProviderController@providerdocument');

    $app->get('/companycountries', 'V1\Common\Admin\Resource\CompanyCountriesController@index');

    $app->post('/companycountries', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CompanyCountriesController@store']);

    $app->get('/companycountries/{id}', 'V1\Common\Admin\Resource\CompanyCountriesController@show');

    $app->patch('/companycountries/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CompanyCountriesController@update']);

    $app->delete('/companycountries/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CompanyCountriesController@destroy']);

    $app->get('/companycountries/{id}/updateStatus', 'V1\Common\Admin\Resource\CompanyCountriesController@updateStatus');

    $app->get('/companycountries/{id}/bankform', 'V1\Common\Admin\Resource\CompanyCountriesController@getBankForm');

    $app->post('/bankform', 'V1\Common\Admin\Resource\CompanyCountriesController@storeBankform');

    //country list
    $app->get('/allcountries', 'V1\Common\Admin\Resource\CompanyCountriesController@allcountries');
    $app->get('/countries', 'V1\Common\Admin\Resource\CompanyCountriesController@countries');
    $app->get('/states/{id}', 'V1\Common\Admin\Resource\CompanyCountriesController@states');
    $app->get('/cities/{id}', 'V1\Common\Admin\Resource\CompanyCountriesController@cities');
    $app->get('/allcities/{id}', 'V1\Common\Admin\Resource\CompanyCountriesController@allcities');
    $app->get('/company_country_list', 'V1\Common\Admin\Resource\CompanyCountriesController@companyCountries');
    $app->get('/vehicle_type_list', 'V1\Transport\Admin\VehicleController@vehicletype');
    //$app->get('/gettaxiprice/{id}', 'V1\Transport\Admin\VehicleController@gettaxiprice');
    
    //CompanyCity
    $app->get('/companycityservice', 'V1\Common\Admin\Resource\CompanyCitiesController@index');

    $app->post('/companycityservice', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CompanyCitiesController@store']);

    $app->get('/companycityservice/{id}', 'V1\Common\Admin\Resource\CompanyCitiesController@show');

    $app->patch('/companycityservice/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CompanyCitiesController@update']);

    $app->delete('/companycityservice/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CompanyCitiesController@destroy']);

    $app->get('/countrycities/{id}', 'V1\Common\Admin\Resource\CompanyCitiesController@countrycities');
    
    //Account setting details
    $app->get('/profile', 'V1\Common\Admin\Resource\AdminController@show_profile');
    $app->post('/profile', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\AdminController@update_profile']);

    Route::get('password', 'V1\Common\Admin\Resource\AdminController@password');
    Route::post('password', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\AdminController@password_update']);

    $app->get('/adminservice', 'V1\Common\Admin\Resource\AdminController@admin_service');
    $app->get('/services/child/list/{id}', 'V1\Common\Admin\Resource\AdminController@child_service');
    $app->get('/heatmap', 'V1\Common\Admin\Resource\AdminController@heatmap');
    $app->get('/godsview', 'V1\Common\Admin\Resource\AdminController@godsview');


    //Admin Seeder
    $app->post('/companyuser', 'V1\Common\Admin\Resource\UserController@companyuser');

    $app->get('/settings', ['uses' => 'V1\Common\Admin\Auth\AdminController@index', 'middleware' => ['permission:site-settings']]);

    $app->post('/settings', ['uses' => 'V1\Common\Admin\Auth\AdminController@settings_store', 'middleware' => ['permission:site-settings', 'demo']]);

    //App Setting
    $app->get('/app-setting', ['uses' => 'V1\Common\Admin\Auth\AdminController@appSetting', 'middleware' => ['permission:site-settings']]);

    $app->post('/app-setting', ['uses' => 'V1\Common\Admin\Auth\AdminController@appSettingStore', 'middleware' => ['permission:site-settings', 'demo']]);

    //Roles   
    $app->get('/roles', ['uses' => 'V1\Common\Admin\Resource\RolesController@index', 'middleware' => ['permission:role-list']]);

    $app->post('/roles', ['uses' => 'V1\Common\Admin\Resource\RolesController@store', 'middleware' => ['permission:role-create']]);

    $app->get('/roles/{id}', ['uses' => 'V1\Common\Admin\Resource\RolesController@show', 'middleware' => ['permission:role-list']]);

    $app->patch('/roles/{id}', ['uses' => 'V1\Common\Admin\Resource\RolesController@update', 'middleware' => ['permission:role-edit', 'demo']]);

    $app->delete('/roles/{id}', ['uses' => 'V1\Common\Admin\Resource\RolesController@destroy', 'middleware' => ['permission:role-delete', 'demo']]);


    //peakhours
    $app->get('/permission', ['uses' => 'V1\Common\Admin\Resource\RolesController@permission', 'middleware' => ['permission:peak-hour-list']]);
    
    $app->get('/peakhour', ['uses' => 'V1\Common\Admin\Resource\PeakHourController@index', 'middleware' => ['permission:peak-hour-list']]);

    $app->post('/peakhour', ['uses' => 'V1\Common\Admin\Resource\PeakHourController@store', 'middleware' => ['permission:peak-hour-create']]);

    $app->get('/peakhour/{id}', ['uses' => 'V1\Common\Admin\Resource\PeakHourController@show', 'middleware' => ['permission:peak-hour-list']]);

    $app->patch('/peakhour/{id}', ['uses' => 'V1\Common\Admin\Resource\PeakHourController@update', 'middleware' => ['permission:peak-hour-edit', 'demo']]);

    $app->delete('/peakhour/{id}', ['uses' => 'V1\Common\Admin\Resource\PeakHourController@destroy', 'middleware' => ['permission:peak-hour-delete', 'demo']]);


    // ratings
    $app->get('/userreview', 'V1\Common\Admin\Resource\AdminController@userReview');

    $app->get('/providerreview', 'V1\Common\Admin\Resource\AdminController@providerReview');

    //Menu
    $app->get('/menu', 'V1\Common\Admin\Resource\MenuController@index');

    $app->post('/menu', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\MenuController@store']);

    $app->get('/menu/{id}', 'V1\Common\Admin\Resource\MenuController@show');

    $app->patch('/menu/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\MenuController@update']);

    $app->delete('/menu/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\MenuController@destroy']);

    $app->patch('/menucity/{id}', 'V1\Common\Admin\Resource\MenuController@menucity');
    $app->get('/ride_type', 'V1\Common\Admin\Resource\MenuController@ride_type');
    $app->get('/service_type', 'V1\Common\Admin\Resource\MenuController@service_type');

    $app->get('/order_type', 'V1\Common\Admin\Resource\MenuController@order_type');
    
    // $app->get('/getcity', 'V1\Common\Admin\Resource\MenuController@getcity');
    $app->get('/getCountryCity/{serviceId}/{CountryId}', 'V1\Common\Admin\Resource\MenuController@getCountryCity');
    $app->get('/getmenucity/{id}', 'V1\Common\Admin\Resource\MenuController@getmenucity');
    //payrolls
    $app->get('/zone', 'V1\Common\Admin\Resource\ZoneController@index');

    $app->post('/zone', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\ZoneController@store']);

    $app->get('/zone/{id}', 'V1\Common\Admin\Resource\ZoneController@show');

    $app->patch('/zone/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\ZoneController@update']);

    $app->delete('/zone/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\ZoneController@destroy']);

    $app->get('/zones/{id}/updateStatus', 'V1\Common\Admin\Resource\ZoneController@updateStatus');

    $app->get('/payroll-template', 'V1\Common\Admin\Resource\PayrollTemplateController@index');

    $app->post('/payroll-template', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PayrollTemplateController@store']);

    $app->get('/payroll-template/{id}', 'V1\Common\Admin\Resource\PayrollTemplateController@show');

    $app->patch('/payroll-template/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PayrollTemplateController@update']);

    $app->delete('/payroll-template/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PayrollTemplateController@destroy']);

    $app->get('/payroll-templates/{id}/updateStatus', 'V1\Common\Admin\Resource\PayrollTemplateController@updateStatus');


    $app->get('/payroll', 'V1\Common\Admin\Resource\PayrollController@index');

    $app->post('/payroll', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PayrollController@store']);

    $app->get('/payroll/{id}', 'V1\Common\Admin\Resource\PayrollController@show');

    $app->patch('/payroll/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PayrollController@update']);

    $app->delete('/payroll/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PayrollController@destroy']);

    $app->get('/payrolls/{id}/updateStatus', 'V1\Common\Admin\Resource\PayrollController@updateStatus');
    
    $app->post('/payroll/update-payroll', 'V1\Common\Admin\Resource\PayrollController@updatePayroll');

    $app->get('/zoneprovider/{type}/{id}', 'V1\Common\Admin\Resource\PayrollController@zoneprovider');
    $app->get('/payrolls/download/{id}', 'V1\Common\Admin\Resource\PayrollController@PayrollDownload');
    $app->get('/cityzones/{id}', 'V1\Common\Admin\Resource\ZoneController@cityzones');
    $app->get('/zonetype/{id}', 'V1\Common\Admin\Resource\ZoneController@cityzonestype');
    Route::get('bankdetails/template', 'V1\Common\Provider\HomeController@template');
    $app->post('/addbankdetails', 'V1\Common\Provider\HomeController@addbankdetails'); 
    $app->post('/editbankdetails', 'V1\Common\Provider\HomeController@editbankdetails'); 

    $app->get('/provider_total_deatils/{id}', 'V1\Common\Admin\Resource\ProviderController@provider_total_deatils');

     $app->get('/dashboard/{id}', 'V1\Common\Admin\Auth\AdminController@dashboarddata');


     $app->get('/statement/provider', 'V1\Common\Admin\Resource\AllStatementController@statement_provider');
     $app->get('/statement/user', 'V1\Common\Admin\Resource\AllStatementController@statement_user');
     $app->get('/transactions', 'V1\Common\Admin\Resource\AllStatementController@statement_admin');
     $app->get('/fleettransactions', 'V1\Common\Admin\Resource\AllStatementController@statement_fleet');

     //search

      $app->get('/getdata', 'V1\Common\Admin\Resource\AllStatementController@getData');
      $app->get('/getfleetprovider', 'V1\Common\Admin\Resource\AllStatementController@getFleetProvider');

      //Owner
      $app->get('/owner', 'V1\Common\Admin\Resource\OwnerController@index');
      $app->post('/owner', 'V1\Common\Admin\Resource\OwnerController@index');
      $app->get('/owner/{id}', 'V1\Common\Admin\Resource\OwnerController@show');
      $app->patch('/owner/{id}', 'V1\Common\Admin\Resource\OwnerController@update');
      $app->get('/owner/{id}/updateStatus', 'V1\Common\Admin\Resource\OwnerController@updateStatus');
      $app->delete('/owner/{id}/delete', 'V1\Common\Admin\Resource\OwnerController@destroy');

      //Cars Type
      $app->get('/cars-type', 'V1\Transport\Admin\CarsController@carstype');
      $app->post('/cars-type', 'V1\Transport\Admin\CarsController@storetype');
      $app->get('/cars-type/{id}', 'V1\Transport\Admin\CarsController@showtype');
      $app->patch('/cars-type/{id}', 'V1\Transport\Admin\CarsController@updatetype');
      $app->get('/cars-type/{id}/updateStatus', 'V1\Transport\Admin\CarsController@updatetypeStatus');
      $app->delete('/cars-type/{id}/delete', 'V1\Transport\Admin\CarsController@destroytype');


      //Cars
      $app->get('/cars', 'V1\Transport\Admin\CarsController@index');
      $app->get('/cars-type-list', 'V1\Transport\Admin\CarsController@carstypeList');
      $app->get('/ownerlist', 'V1\Transport\Admin\CarsController@ownerList');
      $app->post('/cars', 'V1\Transport\Admin\CarsController@store');
      $app->get('/cars/{id}', 'V1\Transport\Admin\CarsController@show');
      $app->patch('/cars/{id}', 'V1\Transport\Admin\CarsController@update');
      $app->get('/cars/{id}', 'V1\Transport\Admin\CarsController@show');
      $app->get('/cars/{id}/updateStatus', 'V1\Transport\Admin\CarsController@updateStatus');
      $app->get('/cars/images/{id}', 'V1\Transport\Admin\CarsController@images');
      $app->patch('/cars/images/{id}', 'V1\Transport\Admin\CarsController@updateimages');
      $app->delete('/cars/{id}/delete', 'V1\Transport\Admin\CarsController@destroy');
      


      //Rental Charges
      $app->get('/rental-charges', 'V1\Transport\Admin\RentalsController@show');
      $app->patch('/rental-charges', 'V1\Transport\Admin\RentalsController@update');


});

$router->get('/payrolls/download/{id}', 'V1\Common\Admin\Resource\PayrollController@PayrollDownload');
$router->get('/searchprovider/{type}/{id}', 'V1\Common\Admin\Resource\ProviderController@searchprovider');
