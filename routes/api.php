<?php

use App\Http\Controllers\Api\ReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\MarqueController;
use App\Http\Controllers\VehiculeController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('password/update', [AuthController::class, 'updatePassword']);
    Route::put('profile/update', [AuthController::class, 'updateProfile']);

    //Entreprise:
    Route::get('/entreprise', [EntrepriseController::class, 'show']);
    Route::post('/entreprise', [EntrepriseController::class, 'store']);
    Route::put('/entreprise', [EntrepriseController::class, 'update']);
    Route::delete('/entreprise', [EntrepriseController::class, 'destroy']);

    //Marque:
    Route::get('/marques', [MarqueController::class, 'index']);
    Route::post('/marques', [MarqueController::class, 'store']);
   
    // Véhicules:
    Route::get('/vehicules', [VehiculeController::class, 'index']);
    Route::post('/vehicules', [VehiculeController::class, 'store']);
    Route::get('/vehicules/{id}', [VehiculeController::class, 'show']);
    Route::put('/vehicules/{id}', [VehiculeController::class, 'update']);
    Route::delete('/vehicules/{id}', [VehiculeController::class, 'destroy']);

    //Reservation:
    Route::post('/reservations/check-availability', [ReservationController::class, 'checkAvailability']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/reservations', [ReservationController::class, 'index']); 
    Route::put('/reservations/{reservation}/status', [ReservationController::class, 'updateStatus']); 


});
