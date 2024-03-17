<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/login', function(){
    return redirect('/');
});
Route::get('/',[UserController::class,'loadLogin']);
Route::post('/login',[UserController::class,'userLogin'])->name('userLogin');

Route::get('/register',[UserController::class,'loadRegister']);
Route::post('/register',[UserController::class,'userRegister'])->name('userRegister');

Route::get('/logout',[UserController::class,'logout']);

Route::get('/home',[UserController::class,'home']);
