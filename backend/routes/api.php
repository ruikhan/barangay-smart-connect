<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\IncidentReportController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ServiceRequestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatbotController;

// ── Public routes ─────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

Route::get('/barangays', function () {
    return response()->json([
        'barangays' => \App\Models\Barangay::where('is_active', true)->get()
    ]);
});

// ── Protected routes ──────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/chatbot', [ChatbotController::class, 'chat']);

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Profile
    Route::put('/profile',          [AuthController::class, 'updateProfile']);
    Route::put('/profile/password', [AuthController::class, 'changePassword']);

    // Services
    Route::apiResource('requests', ServiceRequestController::class)
         ->only(['index', 'store', 'show']);

    // Announcements
    Route::apiResource('announcements', AnnouncementController::class)
         ->only(['index', 'show']);

    // Incidents
    Route::apiResource('incidents', IncidentReportController::class)
         ->only(['index', 'store']);

    // Messages
    Route::get('/messages/conversations',   [MessageController::class, 'conversations']);
    Route::get('/messages/contacts',        [MessageController::class, 'contacts']);
    Route::get('/messages/thread/{userId}', [MessageController::class, 'thread']);
    Route::post('/messages/send',           [MessageController::class, 'send']);

// Broadcasting auth for Sanctum token users
Route::post('/broadcasting/auth', function (\Illuminate\Http\Request $request) {
    $pusher = new \Pusher\Pusher(
        config('broadcasting.connections.pusher.key'),
        config('broadcasting.connections.pusher.secret'),
        config('broadcasting.connections.pusher.app_id'),
        config('broadcasting.connections.pusher.options')
    );

    $auth = $pusher->authorizeChannel(
        $request->channel_name,
        $request->socket_id
    );

    return response($auth);
});

    // ── Admin only ────────────────────────────────────────
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/stats',                  [AdminController::class, 'stats']);
        Route::get('/requests',               [AdminController::class, 'requests']);
        Route::patch('/requests/{id}/status', [AdminController::class, 'updateRequest']);
        Route::get('/residents',              [AdminController::class, 'residents']);
        Route::post('/announcements',         [AdminController::class, 'createAnnouncement']);
    });

});