<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FaceRecognitionController;

// Route::middleware('auth:sanctum')->group(function () {
   
// });
Route::post('/register', [FaceRecognitionController::class, 'register']);
Route::post('/train', [FaceRecognitionController::class, 'train']);
Route::post('/login', [FaceRecognitionController::class, 'login']);