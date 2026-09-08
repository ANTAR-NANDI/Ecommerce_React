<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');
