<?php

use App\Http\Controllers\PartageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/partage/{token}', PartageController::class);
