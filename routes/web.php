<?php

use App\Http\Controllers\TransaksiAdrController;
use App\Models\TransaksiAdr;
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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/adr/next', [TransaksiAdrController::class, 'next'])->name('perkiraan.next');
// Tambahkan route untuk download Excel
Route::post('/adr/download-excel', [TransaksiAdrController::class, 'downloadExcel'])->name('adr.download.excel');
Route::resource('adr', TransaksiAdrController::class);
