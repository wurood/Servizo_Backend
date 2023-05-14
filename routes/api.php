<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShopesController;
use App\Http\Controllers\RequestsController;
use App\Http\Controllers\MessagesController;


Route::controller(AuthController::class)->group(function () {
    Route::post('/auth/login', 'login');
    Route::post('/auth/register', 'register');
    Route::post('/auth/logout', 'logout');
    Route::post('/auth/refresh', 'refresh');
    Route::post('/auth/refresh', 'refresh');
    Route::post('/auth/update', 'update');
    Route::post('/auth/delete', 'delete');

});

Route::controller(ShopesController::class)->group(function () {
    Route::get('/shop/get', 'get');
    Route::post('/shop/add', 'add');
    Route::post('/shop/update', 'update');
    Route::get('/shop/show', 'show');
    Route::post('/shop/delete', 'delete');
});

Route::controller(RequestsController::class)->group(function () {
    Route::get('/request/get', 'get');
    Route::post('/request/add', 'add');
    Route::post('/request/update', 'update');
    Route::get('/request/show', 'show');
    Route::post('/request/delete', 'delete');
});

Route::controller(MessagesController::class)->group(function () {
    Route::get('/message/get', 'get');
    Route::post('/message/add', 'add');
    Route::get('/message/show', 'show');
    Route::post('/message/delete', 'delete');
});


