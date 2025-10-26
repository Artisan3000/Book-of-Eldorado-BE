<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\StudentCourseController;
use App\Http\Controllers\StudentAchievementController;
use App\Http\Controllers\StudentExploreController;
use App\Http\Controllers\StudentOverviewController;
use App\Http\Middleware\Role;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

Route::middleware('auth:sanctum')->group(function () {

    // STUDENT: Own courses
     Route::get('/student/overview', [StudentOverviewController::class, 'getMyOverview']);
    Route::get('/student/courses', [StudentCourseController::class, 'getMyCourses']);
    Route::get('/student/courses/{slug}', [StudentCourseController::class, 'getMyCourseDetails']);
    Route::get('/student/achievements', [StudentAchievementController::class,'getMyAchievements']);
    Route::get('/student/explore', [StudentExploreController::class,'getMyExplore']);
   

    // ADMIN: Any student (only accessible to users with role 'admin')
    Route::prefix('admin')->middleware(Role::class . ':admin')->group(function () {
        Route::get('/student/{id}/courses', [StudentCourseController::class, 'getCourses']);
        Route::get('/student/{id}/courses/{slug}', [StudentCourseController::class, 'getCourseDetails']);
        Route::get('/student/{id}/achievements', [StudentAchievementController::class,'getAchievements']);
        Route::get('/student/{id}/explore', [StudentExploreController::class,'getExplore']);
        Route::get('/student/{id}/overview', [StudentOverviewController::class, 'getOverview']);
    });

    
});
