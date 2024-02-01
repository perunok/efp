<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotController;
use App\Http\Controllers\HomeController;

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


Route::get('logout', function () {
    return redirect('/');
})->name('logout');

Route::get('/', [HomeController::class, 'index'])->name('index');

Route::get('new_broadcast', function () {
    return view('new_broadcast_form');
})->name('new_broadcast');

Route::post('broadcast/new', [HomeController::class, 'makeBroadcast'])->name('make_broadcast');
Route::post('broadcast/edit', [HomeController::class, 'editBroadcast'])->name('broadcasted_list');
Route::get('broadcast_get', [HomeController::class, 'getBroadcast']);
Route::get('rebroadcast', [HomeController::class, 'rebroadcast']);
Route::get('broadcasted_list', [HomeController::class, 'showBroadcasted'])->name('broadcasted_list');
Route::get('tip/bookmark', [HomeController::class, 'bookmark']);


Route::get('reporting', function () {
    return view('reporting');
})->name('reporting');


// bot related routes
Route::get('setwebhook', [BotController::class, 'setWebhook']);
Route::get('test', [BotController::class, 'index']);
Route::post('/bot', [BotController::class, 'webhookUpdate']);




// ssh -R 80:localhost:8000 serveo.net
