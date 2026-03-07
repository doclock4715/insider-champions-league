<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeagueController;

Route::get('/status', [LeagueController::class, 'getStatus']);
Route::post('/play-week', [LeagueController::class, 'playWeek']);
Route::post('/play-all', [LeagueController::class, 'playAllWeeks']);
Route::post('/reset', [LeagueController::class, 'reset']);
Route::put('/matches/{id}', [LeagueController::class, 'updateMatch']);