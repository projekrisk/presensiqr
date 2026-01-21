<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterSchoolController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/register-school', [RegisterSchoolController::class, 'store'])->name('register.school');