<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DirectorController;

// ------------------ Change Password

Route::post('/changePassword',[DirectorController::class,'changePassword']);

// ------------------ Reset Password

Route::post('/resetPassword',[DirectorController::class,'resetPassword']);

// ------------------ Email Verify

Route::post('/emailVerify',[DirectorController::class,'emailVerify']);

// ------------------ Auth Director

Route::post("/login",[DirectorController::class,"login"])->middleware("guest:sanctum");
Route::delete('/logout/{token?}',[DirectorController::class,'logout']);
Route::get('/',[DirectorController::class,'index']);

// ------------------ Crud Formatteur

Route::get('/showFormatteurs',[DirectorController::class,'showFormatteurs']);
Route::post('/addFormatteur',[DirectorController::class,'addFormatteur']);
Route::post('/updateFormatteur/{id}',[DirectorController::class,'updateFormatteur']);
Route::get('/showFormatteur/{id}',[DirectorController::class,'showFormatteur']);
Route::delete('/deleteFormatteur/{id}',[DirectorController::class,'deleteFormatteur']);

// ------------------ Crud Tutteur

Route::get('/showTutteurs',[DirectorController::class,'showTutteurs']);
Route::post('/addTutteur',[DirectorController::class,'addTutteur']);
Route::post('/updateTutteur/{id}',[DirectorController::class,'updateTutteur']);
Route::get('/showTutteur/{id}',[DirectorController::class,'showTutteur']);
Route::delete('/deleteTutteur/{id}',[DirectorController::class,'deleteTutteur']);

// ------------------ Crud Filiere

Route::get('/showFilieres',[DirectorController::class,'showFilieres']);
Route::post('/addFiliere',[DirectorController::class,'addFiliere']);
Route::post('/updateFiliere/{id}',[DirectorController::class,'updateFiliere']);
Route::get('/showFiliere/{id}',[DirectorController::class,'showFiliere']);
Route::delete('/deleteFiliere/{id}',[DirectorController::class,'deleteFiliere']);

// ------------------ Show Stagiaires and Modules


Route::get('/showStagiaires',[DirectorController::class,'showStagiaires']);
Route::get('/showModules',[DirectorController::class,'showModules']);

