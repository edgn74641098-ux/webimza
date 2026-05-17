<?php

use App\Http\Controllers\Api\AddinRegisterController;
use App\Http\Controllers\Api\HeartbeatController;
use App\Http\Controllers\Api\SignatureCheckController;
use App\Http\Controllers\Api\SignatureReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('addin')->group(function () {
    Route::post('/register', AddinRegisterController::class);
    Route::post('/signature/check', SignatureCheckController::class);
    Route::post('/signature/report', SignatureReportController::class);
    Route::post('/heartbeat', HeartbeatController::class);
});
