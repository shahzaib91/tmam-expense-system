<?php

use App\AccountingDrivers\Helper\QuickBooksAuthHelper;
use App\Transactions;
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
Route::get('/', 'FrontendController@index');
Route::get('/list/{merchantID}', 'FrontendController@list');
Route::get('/auth', 'FrontendController@quickBooksAuth');
Route::get('/quickbooks-response', 'FrontendController@handleQuickBooksAuth');
