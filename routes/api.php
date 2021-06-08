<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function () {
    return res(null, 'API v2 is running...');
});

Route::get('/unauthenticated', function () {
    return eRes('Unauthenticated', 401);
})->name('unauthenticated');

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/verify-email', [UserController::class, 'verifyEmail']);
    Route::post('/check-token', [UserController::class, 'checkToken']);
    Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
    Route::post('/set-new-password', [UserController::class, 'setNewPassword']);
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/logout', [UserController::class, 'logout']);
    });
});
