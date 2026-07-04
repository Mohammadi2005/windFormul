<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\FormulController;

Route::post('store',[FormulController::class,'store']);
Route::get('show',[FormulController::class,'show']);
Route::get('calc',[FormulController::class,'calc']);
