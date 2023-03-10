<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/reservation', [App\Http\Controllers\ReservationController::class, 'index'])->name('reservation');
Route::post('/reservation/insertPayment', [App\Http\Controllers\ReservationController::class, 'insertPayment'])->name('insert-payment');
Route::get('/reservation/getTableDetailData', [App\Http\Controllers\ReservationController::class, 'getTableDetailData'])->name('get-table-detail-data');
Route::get('/about-us', [App\Http\Controllers\AboutUsController::class, 'index'])->name('about-us');
Route::get('/coffee', [App\Http\Controllers\CoffeeController::class, 'index'])->name('coffee');
Route::get('/menu', [App\Http\Controllers\MenuController::class, 'index'])->name('menu');
Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
Route::post('/edit-profile', [App\Http\Controllers\ProfileController::class, 'editProfile'])->name('edit-profile');
