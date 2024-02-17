<?php

use App\Http\Controllers\TutteurController;
use Illuminate\Support\Facades\Route;

// ------------------ Change Password

Route::post('/changePassword',[TutteurController::class,'changePassword']);

// ------------------ Reset Password

Route::post('/resetPassword',[TutteurController::class,'resetPassword']);

// ------------------ Email Verify

Route::post('/emailVerify',[TutteurController::class,'emailVerify']);


// ------------------ Auth Tutteur
Route::post("/login",[TutteurController::class,"login"])->middleware("guest:sanctum");
Route::delete('/logout/{token?}',[TutteurController::class,'logout']);
Route::get('/',[TutteurController::class,'index']);

// ------------------ CRUD Stagiaire

Route::get('/showStagiaires',[TutteurController::class,'showStagiaires']);
Route::post('/addStagiaire',[TutteurController::class,'addStagiaire']);
Route::post('/updateStagiaire/{id}',[TutteurController::class,'updateStagiaire']);
Route::get('/showStagiaire/{id}',[TutteurController::class,'showStagiaire']);
Route::delete('/deleteStagiaire/{id}',[TutteurController::class,'deleteStagiaire']);

// ------------------ Crud Module

Route::get('/showModules',[TutteurController::class,'showModules']);
Route::post('/addModule',[TutteurController::class,'addModule']);
Route::post('/updateModule/{id}',[TutteurController::class,'updateModule']);
Route::get('/showModule/{id}',[TutteurController::class,'showModule']);
Route::delete('/deleteModule/{id}',[TutteurController::class,'deleteModule']);

// ------------------ Crud Formateur_filliere_module

Route::get('/showFormatteurFiliereModules',[TutteurController::class,'showFormatteurFiliereModules']);
Route::post('/addFormatteurFiliereModule',[TutteurController::class,'addFormatteurFiliereModule']);
Route::post('/updateFormatteurFiliereModule/{id}',[TutteurController::class,'updateFormatteurFiliereModule']);
Route::delete('/deleteFormatteurFiliereModule/{id}',[TutteurController::class,'deleteFormatteurFiliereModule']);

// ------------------ Crud Stagiaire_module

Route::get('/showStagiaireModules',[TutteurController::class,'showStagiaireModules']);
Route::post('/addStagiaireModule',[TutteurController::class,'addStagiaireModule']);
Route::post('/updateStagiaireModule/{id}',[TutteurController::class,'updateStagiaireModule']);
Route::delete('/deleteStagiaireModule/{id}',[TutteurController::class,'deleteStagiaireModule']);

// ------------------ Crud Absence

Route::get('/showAbsences',[TutteurController::class,'showAbsences']);
Route::post('/addAbsence',[TutteurController::class,'addAbsence']);
Route::get('/showAbsence/{id}',[TutteurController::class,'showAbsence']);
Route::post('/updateAbsence/{id}',[TutteurController::class,'updateAbsence']);
Route::delete('/deleteAbsence/{id}',[TutteurController::class,'deleteAbsence']);

// ------------------ Affiche Stagiaire Notes
Route::get('/showStagiaireInfo',[TutteurController::class,'showStagiaireInfo']);
Route::get('/showStagiaireNotes',[TutteurController::class,'showStagiaireNotes']);

