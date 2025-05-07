<?php

require_once __DIR__.'/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(dirname(__DIR__)))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades(true,
    [
        Laravel\Socialite\Facades\Socialite::class => 'Socialite',
        Davibennun\LaravelPushNotification\Facades\PushNotification::class => 'PushNotification',
        Illuminate\Support\Facades\Notification::class => 'Notification',
        \App\Providers\WebPushServiceProvider::class => 'WebPush'
    ]);

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

// $app->singleton('mailer', function ($app) { 
//     $app->configure('services'); 
//     return $app->loadComponent('mail', 'Illuminate\Mail\MailServiceProvider', 'mailer'); 
// });

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Filesystem\Factory::class,
    function ($app) {
        return new Illuminate\Filesystem\FilesystemManager($app);
    }
);

$app->singleton(
    Illuminate\Contracts\Filesystem\Factory::class,
    function ($app) {
        return new Illuminate\Filesystem\FilesystemManager($app);
    }
 );

$app->configure('jwt');
$app->configure('cors');
$app->configure('auth');
$app->configure('logging');
$app->configure('settings');
$app->configure('database');
$app->configure('permission');
$app->configure('filesystems');
$app->configure('swagger-lume');
$app->configure('webpush');

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
        // App\Http\Middleware\HandleRequest::class,
	   Barryvdh\Cors\HandleCors::class
]);

$app->routeMiddleware([
     'auth' => App\Http\Middleware\Authenticate::class,
     'authless' => App\Http\Middleware\AuthLess::class,
     'permission' => Spatie\Permission\Middlewares\PermissionMiddleware::class,
     'role'       => Spatie\Permission\Middlewares\RoleMiddleware::class,
     'demo'       => App\Http\Middleware\DemoModeMiddleware::class
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

// $app->register(App\Providers\AppServiceProvider::class);
$app->register(
    App\Providers\AuthServiceProvider::class,
    App\Providers\AppServiceProvider::class
);
// $app->register(App\Providers\EventServiceProvider::class);

$app->register(\Tymon\JWTAuth\Providers\LumenServiceProvider::class);
$app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);
$app->register(Barryvdh\Cors\ServiceProvider::class);
$app->register(App\Providers\CustomMailServiceProvider::class);

$app->register(Illuminate\Notifications\NotificationServiceProvider::class);

$app->register(Spatie\Permission\PermissionServiceProvider::class);
$app->register(Laravel\Socialite\SocialiteServiceProvider::class);
$app->register(Davibennun\LaravelPushNotification\LaravelPushNotificationServiceProvider::class);
$app->register(\App\Providers\WebPushServiceProvider::class);
$app->register(Maatwebsite\Excel\ExcelServiceProvider::class);
$app->register(\SwaggerLume\ServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->alias('cache', \Illuminate\Cache\CacheManager::class);
$app->configure('mail');
$app->configure('gride');
$app->configure('view'); 
$app->alias('mailer', Illuminate\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\MailQueue::class);
$app->alias('Excel', Maatwebsite\Excel\Facades\Excel::class);

$app->register(Illuminate\Redis\RedisServiceProvider::class);
/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/v1/web.php';
});

$app->router->group([
    'prefix' => 'api/v1/admin',
    'as' => 'admin.',
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/v1/admin.php';
});

$app->router->group([
	'prefix' => 'api/v1/shop',
    'as' => 'shop.',
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/v1/order.php';
});

$app->router->group([
    'prefix' => 'api/v1/user',
    'as' => 'user.',
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/v1/user.php';
});

$app->router->group([
    'prefix' => 'api/v1/provider',
    'as' => 'provider.',
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/v1/provider.php';
});



/*$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/v2/web.php';
});

$app->router->group([
    'prefix' => 'api/v2/admin',
    'as' => 'admin.',
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/v2/admin.php';
});

$app->router->group([
    'prefix' => 'api/v2/shop',
    'as' => 'shop.',
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/v2/order.php';
});

$app->router->group([
    'prefix' => 'api/v2/user',
    'as' => 'user.',
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/v2/user.php';
});

$app->router->group([
    'prefix' => 'api/v2/provider',
    'as' => 'provider.',
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/v2/provider.php';
});

*/



return $app;
