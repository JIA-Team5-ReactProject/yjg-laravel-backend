<?php

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

Route::get('qrcode', function () {

    // $url에 주소적고 generate에다 넣으면 url QR코드
    // $url = 'https://www.google.co.kr/';
    // return QrCode::size(300)->generate($url);

    //generate에 문자열을 넣으면 그대로 QR에 값이 들어감
    //return QrCode::size(300)->generate('A basic example of QR code!');

    
});