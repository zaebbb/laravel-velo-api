<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CharacterProductController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\TableProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users/{id}/up', [UserController::class, 'update']);
Route::post('/users/{id}/del', [UserController::class, 'destroy']);

Route::get('/news', [NewsController::class, 'index']);
Route::post('/news', [NewsController::class, 'store']);
Route::get('/news/{id}', [NewsController::class, 'show']);
Route::post('/news/{id}/up', [NewsController::class, 'update']);
Route::post('/news/{id}/del', [NewsController::class, 'destroy']);

Route::get('/products', [ProductsController::class, 'index']);
Route::post('/products', [ProductsController::class, 'store']);
Route::get('/products/{id}', [ProductsController::class, 'show']);
Route::post('/products/{id}/up', [ProductsController::class, 'update']);
Route::post('/products/{id}/del', [ProductsController::class, 'destroy']);

Route::get('/products/{idProduct}/table', [TableProductController::class, 'index']);
Route::post('/products/{idProduct}/table', [TableProductController::class, 'store']);
Route::get('/products/{idProduct}/table/items', [TableProductController::class, 'show']);
Route::post('/products/{idProduct}/table/items/{idItemTable}/up', [TableProductController::class, 'update']);
Route::post('/products/{idProduct}/table/items/{idItemTable}/del', [TableProductController::class, 'destroy']);

Route::post('/auth/login', [AuthController::class, 'index']);
Route::post('/auth/exit', [AuthController::class, 'store']);

Route::get('/products/characters/all', [CharacterProductController::class, 'index']);
Route::get('/products/{idProduct}/character', [CharacterProductController::class, 'show']);
Route::post('/products/{idProduct}/character/create', [CharacterProductController::class, 'store']);
Route::post('/products/{idProduct}/character/{idCharacter}/del', [CharacterProductController::class, 'destroy']);
Route::post('/products/{idProduct}/character/{idCharacter}/up', [CharacterProductController::class, 'update']);
