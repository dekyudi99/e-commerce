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

    // Contents Management
    $router->post('/contents/save', 'ContentsController@store');
    $router->post('/contents/update/{id}', 'ContentsController@update');
    $router->delete('/contents/delete/{id}', 'ContentsController@destroy');
    
    // Verifikasi user
    $router->post('/shop/validasi/{id}', 'ShopController@validasi');

    // Melihat semua data
    $router->get('/order/getall', 'OrdersController@orders');

    // Melihat semua product
    $router->get('/product/getall', 'ProductsController@index');

    // Melihat semua toko dan detail
    $router->get('/shop/shopall', 'ShopController@showAll');
    $router->get('/shop/detail/{id}', 'ShopController@showDetail');
    $router->delete('/shop/delete/{id}', 'ShopController@destroy');
});

$router->group(['middleware' => ['auth:api', 'role:normal,admin']], function () use ($router) {
    // Profile User
    $router->post('/user-profile', 'AuthController@me');
    $router->post('/users/updateProfile', 'UsersController@updateMe');
    $router->delete('/users/deleteProfile', 'UsersController@deleteMe');

    // Contents view
    $router->get('/contents/getall', 'ContentsController@index');
    $router->get('/contents/detail/{id}', 'ContentsController@show');
    
    //Refresh Token
    $router->post('/refresh', 'AuthController@refresh');

    //Logout
    $router->post('/logout', 'AuthController@logout');
    
    // Progress User
    $router->post('/progress/{id}', 'UserProgressController@index');
    
    // Get & Detail & Delete Product
    $router->get('/product/getall', 'ProductsController@index');
    $router->get('/product/detail/{id}', 'ProductsController@show');
    $router->delete('/product/delete/{id}', 'ProductsController@destroy');
});

$router->group(['middleware' => ['auth:api', 'role:normal']], function () use ($router) {
    // Management Product
    $router->get('/product/myproduct', 'ProductsController@myProducts');
    $router->post('/product/save', 'ProductsController@store');
    $router->post('/product/update/{id}', 'ProductsController@update');

    // Membuat dan menampilkan toko
    $router->post('/shop/save', 'ShopController@store');
    $router->get('/shop/show', 'ShopController@show');
    $router->post('/shop/update', 'ShopController@update');
    $router->get('/shop/orders', 'OrdersController@orderShop');

    // Menambahkan dan melihat product ke cart
    $router->post('/order/cart/{id}', 'OrdersController@cart');
    $router->get('/order/cart', 'OrdersController@mycart');

    // Pesan product dari cart
    $router->post('/order/orderCart', 'OrdersController@orderCart');

    // Pesan product langsung
    $router->post('/order/directOrder/{id}', 'OrdersController@directOrder');

    // Menampilkan pesanan anda
    $router->get('/order/myorder', 'OrdersController@myorder');

    // Rute ini akan dipanggil oleh Flutter saat user menekan tombol bayar
});

// Rute ini akan dipanggil oleh server Midtrans
$router->post('/orders/{id}/pay', 'PaymentController@createMidtransTransaction');
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