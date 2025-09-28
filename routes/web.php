<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Login & Register
$router->post('/login', 'AuthController@login');
$router->post('/register', 'AuthController@register');

$router->group(['middleware' => ['auth:api', 'role:admin']], function () use ($router) {
    // User Management
    $router->get('/users/getall', 'UsersController@index');
    $router->get('/users/detail/{id}', 'UsersController@show');
    $router->delete('/users/delete/{id}', 'UsersController@destroy');
    
    // Verifikasi user
    $router->post('/shop/validasi/{id}', 'ShopController@validasi');

    // Melihat semua order
    $router->get('/order/getall', 'OrdersController@orders');

    // Hapus User
    $router->delete('/users/deleteProfile', 'UsersController@deleteMe');
});

$router->group(['middleware' => ['auth:api', 'role:farmer,worker,driver,admin']], function () use ($router) {
    // Profile User
    $router->get('/user-profile', 'AuthController@me');
    $router->post('/users/updateProfile', 'UsersController@updateMe');
    
    //Refresh Token
    $router->post('/refresh', 'AuthController@refresh');

    //Logout
    $router->post('/logout', 'AuthController@logout');
    
    // Hapus Product
    $router->delete('/product/delete/{id}', 'ProductsController@destroy');
});

$router->group(['middleware' => ['auth:api', 'role:farmer,worker,driver']], function () use ($router) {
    // Menambahkan dan melihat product ke cart
    $router->post('/cart/add/{id}', 'OrdersController@cart');
    $router->get('/cart/mycart', 'OrdersController@mycart');

    // Pesan product dari cart
    $router->post('/cart/order', 'OrdersController@orderCart');

    // Pesan product langsung
    $router->post('/order/direct/{id}', 'OrdersController@directOrder');
    
    // Menampilkan pesanan anda
    $router->get('/order/myorder', 'OrdersController@myorder');

    //Menambah dan Update Review
    $router->post('/review/add/{id}', 'ReviewController@store');
    $router->post('/review/update/{id}', 'ReviewController@update');
});

$router->group(['middleware' => ['auth:api', 'role:farmer']], function () use ($router) {
    // Management Product
    $router->get('/product/myproduct', 'ProductsController@myProducts');
    $router->post('/product/add', 'ProductsController@store');
    $router->post('/product/update/{id}', 'ProductsController@update');
    
    // Menampilkan pesanan masuk
    $router->get('/order/in', 'OrdersController@orderIn');
});

// Get & Detail Product
$router->get('/product/getall', 'ProductsController@index');
$router->get('/product/show/{id}', 'ProductsController@show');

// Rute ini akan dipanggil oleh server Midtrans
$router->post('/payment/order/{id}', 'PaymentController@createMidtransTransaction');
$router->post('/midtrans/callback', 'MidtransCallbackController@handle');

// $router->get('/profile/{filename}', function ($filename) {
//     $path = storage_path('app/public/profile/'.$filename.'.jpg');

//     if (!file_exists($path)) {
//         abort(404, 'File not found');
//     }

//     return response()->file($path);
// });

// $router->get('/products/{filename}', function ($filename) {
//     $path = storage_path('app/public/product/' . $filename. '.jpg');

//     if (!file_exists($path)) {
//         abort(404, 'File not found');
//     }

//     return response()->file($path);
// });