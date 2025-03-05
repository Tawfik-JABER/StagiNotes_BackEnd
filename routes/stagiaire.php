<?php

use App\Http\Controllers\StagiaireController;
use Illuminate\Support\Facades\Route;

// ------------------ Change Password

Route::post('/changePassword',[StagiaireController::class,'changePassword']);

// ------------------ Reset Password

Route::post('/resetPassword',[StagiaireController::class,'resetPassword']);

// ------------------ Email Verify

Route::post('/emailVerify',[StagiaireController::class,'emailVerify']);

// ------------------ Auth Stagiaire

Route::post("/login",[StagiaireController::class,"login"])->middleware("guest:sanctum");
Route::delete('/logout/{token?}',[StagiaireController::class,'logout']);
Route::get('/',[StagiaireController::class,'index']);

Route::get('/showNotes',[StagiaireController::class,'showNotes']);

// ------------------ Profile image (Upload / Delete)

Route::post('/uploadProfileImage', [StagiaireController::class, 'uploadProfileImage']);
Route::delete('/deleteProfileImage', [StagiaireController::class, 'deleteProfileImage']);
