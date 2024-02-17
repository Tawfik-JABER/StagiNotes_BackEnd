<?php

use App\Http\Controllers\FormatteurController;
use Illuminate\Support\Facades\Route;

// ------------------ Change Password

Route::post('/changePassword',[FormatteurController::class,'changePassword']);

// ------------------ Reset Password

Route::post('/resetPassword',[FormatteurController::class,'resetPassword']);

// ------------------ Email Verify

Route::post('/emailVerify',[FormatteurController::class,'emailVerify']);

// ------------------ Auth Formatteur

Route::post("/login",[FormatteurController::class,"login"])->middleware("guest:sanctum");
Route::delete('/logout/{token?}',[FormatteurController::class,'logout']);
Route::get('/',[FormatteurController::class,'index']);

Route::get('/form_data', [FormatteurController::class,"form_data"]);

Route::post('/stagiaires', [FormatteurController::class,"stagiaires"]);

Route::post('/store_note', [FormatteurController::class,"store_note"]);
