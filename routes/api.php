<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

// use Illuminate\Support\Str;

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

Route::prefix('v1')->group(function () {
    Route::middleware('jwt')->group(function(){
        Route::post('/bidang', 'Master\BidangController@add');
        Route::patch('/bidang/{id}', 'Master\BidangController@update')->where('id', '[0-9]+');
        Route::delete('/bidang/{id}', 'Master\BidangController@delete')->where('id', '[0-9]+');

        Route::get('/kategori_bidang', 'Master\BidangController@getCategories');
        Route::post('/kategori_bidang', 'Master\BidangController@addCategory');
        Route::patch('/kategori_bidang/{id}', 'Master\BidangController@updateCategory')->where('id', '[0-9]+');
        Route::delete('/kategori_bidang/{id}', 'Master\BidangController@deleteCategory')->where('id', '[0-9]+');

        Route::patch('/harga/{id}', 'Master\HargaController@update')->where('id', '[0-9]+');

        Route::get('/pt', 'PTController@index');
        Route::get('/pt/{id}', 'PTController@show')->where('id', '[0-9]+');
        Route::get('/pt/{id}/file', 'PTController@getFile')->where('id', '[0-9]+');
        Route::post('/pt/{id}', 'PTController@update')->where('id', '[0-9]+');
        Route::delete('/pt/{id}', 'PTController@destroy');
        Route::get('/pt/{id}/{file}/download', 'PTController@downloadFile')->where(['id' =>'[0-9]+', 'file' => '(ktp|npwp)']);

        Route::get('/cv', 'CVController@index');
        Route::get('/cv/{id}', 'CVController@show')->where('id', '[0-9]+');
        Route::get('/cv/{id}/file', 'CVController@getFile')->where('id', '[0-9]+');
        Route::post('/cv/{id}', 'CVController@update')->where('id', '[0-9]+');
        Route::delete('/cv/{id}', 'CVController@destroy');
        Route::get('/cv/{id}/{file}/download', 'CVController@downloadFile')->where(['id' => '[0-9]+', 'file' => '(ktp|npwp)']);
        
        Route::get('/koperasi', 'KoperasiController@index');
        Route::get('/koperasi/{id}', 'KoperasiController@show')->where('id', '[0-9]+');
        Route::get('/koperasi/{id}/file', 'KoperasiController@getFile')->where('id', '[0-9]+');
        Route::post('/koperasi/{id}', 'KoperasiController@update')->where('id', '[0-9]+');
        Route::delete('/koperasi/{id}', 'KoperasiController@destroy');
        Route::get('/koperasi/{id}/{file}/download', 'KoperasiController@downloadFile')->where(['id' => '[0-9]+', 'file' => '(ktp|npwp)']);
        Route::get('/koperasi/{id}/download', 'KoperasiController@downloadFile')->where(['id' => '[0-9]+']);

        Route::get('/yayasan', 'YayasanController@index');
        Route::get('/yayasan/{id}', 'YayasanController@show')->where('id', '[0-9]+');
        Route::get('/yayasan/{id}/file', 'YayasanController@getFile')->where('id', '[0-9]+');
        Route::post('/yayasan/{id}', 'YayasanController@update')->where('id', '[0-9]+');
        Route::delete('/yayasan/{id}', 'YayasanController@destroy');
        Route::get('/yayasan/{id}/{file}/download', 'YayasanController@downloadFile')->where(['id' => '[0-9]+', 'file' => '(ktp|npwp)']);

        Route::get('/firma', 'FirmaController@index');
        Route::get('/firma/{id}', 'FirmaController@show')->where('id', '[0-9]+');
        Route::get('/firma/{id}/file', 'FirmaController@getFile')->where('id', '[0-9]+');
        Route::post('/firma/{id}', 'FirmaController@update')->where('id', '[0-9]+');
        Route::delete('/firma/{id}', 'FirmaController@destroy');
        Route::get('/firma/{id}/{file}/download', 'FirmaController@downloadFile')->where(['id' => '[0-9]+', 'file' => '(ktp|npwp)']);

        Route::get('/perkumpulan', 'PerkumpulanController@index');
        Route::get('/perkumpulan/{id}', 'PerkumpulanController@show')->where('id', '[0-9]+');
        Route::get('/perkumpulan/{id}/file', 'PerkumpulanController@getFile')->where('id', '[0-9]+');
        Route::post('/perkumpulan/{id}', 'PerkumpulanController@update')->where('id', '[0-9]+');
        Route::delete('/perkumpulan/{id}', 'PerkumpulanController@destroy');
        Route::get('/perkumpulan/{id}/{file}/download', 'PerkumpulanController@downloadFile')->where(['id' => '[0-9]+', 'file' => '(ktp|npwp)']);

        Route::post('/admin/change-password', 'Auth\LoginController@changePassword');
    });

    Route::get('/harga', 'Master\HargaController@index');
    Route::get('/harga/{tipe?}', 'Master\HargaController@index');
    Route::get('/bidang', 'Master\BidangController@get');
    Route::get('/provinces', 'Master\LocationController@getProvinces');
    Route::get('/regencies', 'Master\LocationController@getRegencies');
    Route::get('/districts', 'Master\LocationController@getDistricts');
    Route::get('/urbans', 'Master\LocationController@getUrbans');
    
    Route::post('/login', 'Auth\LoginController@index');

    Route::post('/pt', 'PTController@store');
    Route::post('/cv', 'CVController@store');
    Route::post('/koperasi', 'KoperasiController@store');
    Route::post('/yayasan', 'YayasanController@store');
    Route::post('/firma', 'FirmaController@store');
    Route::post('/perkumpulan', 'PerkumpulanController@store');
    
    Route::get('/migrate/rollback', function () {
        Artisan::call('migrate:rollback');
        dd(Artisan::output());
    });
    Route::get('/migrate/', function () {
        Artisan::call('migrate');
        dd(Artisan::output());
    });

});

Route::fallback(function () {
    return response()->json(['message' => 'Route request is not found'], 404);
});

// Route::get('/phpinfo', function () {
//     phpinfo();
//     die();
// });

// Route::get(
//     '/key', function () {
//         // return base64_decode();
//         // return base64_encode();
//         return Str::random(32);
//     }
// );

