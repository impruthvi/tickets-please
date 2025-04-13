<?php

use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->apiResource('tickets', TicketController::class);
    Route::middleware('auth:sanctum')->apiResource('users', UserController::class);
});
