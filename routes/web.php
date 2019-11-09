<?php

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

//Route::get('/', 'PagesController@root')->name('root')->middleware('verified');
Route::redirect('/','/products?pagae=1')->name('root');
Route::get('products','ProductsController@index')->name('products.index');
Route::get('products/{product}', 'ProductsController@show')->name('products.show');


Auth::routes(['verify' => true]);

