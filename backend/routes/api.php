<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\AuthController;
use App\Http\Controllers\Api\User\QuizController;
use App\Http\Controllers\Api\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\Api\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Api\Admin\DashboardController;
// use App\Http\Controllers\Api\QuizController;

// Routes publiques (Auth)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées par Sanctum
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Tes routes Quiz
    Route::get('/quiz/{attempt}/questions', [QuizController::class, 'getQuizQuestions']);
    Route::get('/quiz', [QuizController::class, 'index']);
    Route::post('/quiz/start', [QuizController::class, 'start']);
    Route::get('/quiz/{attempt}/question/{number}', [QuizController::class, 'getQuestion']);
    Route::post('/quiz/submit-answer', [QuizController::class, 'submitAnswer']);
    Route::post('/quiz/finish', [QuizController::class, 'finish']);
    Route::get('/quiz/result/{attempt}', [QuizController::class, 'result']);
    Route::get('/leaderboard', [QuizController::class, 'leaderboard']);
    Route::get('/quiz/my-scores', [QuizController::class, 'myScores']);

    Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {

            Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
            // Gestion des Quizzes et Questions
            Route::apiResource('quizzes', AdminQuizController::class);
            Route::apiResource('questions', AdminQuestionController::class);
            
    });
    // ROUTES SUPPLÉMENTAIRES 
    Route::get('/my-attempts', [QuizController::class, 'myAttempts']);
});