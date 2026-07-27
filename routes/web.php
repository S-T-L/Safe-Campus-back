<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Backend API is running',
        'appName' => config('app.name'),
    ]);
});
