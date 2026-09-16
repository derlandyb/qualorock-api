<?php

use App\Presentation\Http\Controllers\Organizer\AuthController;
use App\Presentation\Http\Controllers\Organizer\EventController;
use App\Presentation\Http\Controllers\SuperAdmin\OrganizerApprovalController;
use Illuminate\Support\Facades\Route;

Route::prefix('super-admin')->group(function (): void {
    Route::get('organizers', [OrganizerApprovalController::class, 'index']);
    Route::post('organizers/{organizer}/approve', [OrganizerApprovalController::class, 'approve']);
    Route::post('organizers/{organizer}/reject', [OrganizerApprovalController::class, 'reject']);
});

Route::prefix('organizer')->group(function (): void {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware(['auth:organizer', 'organizer.approved'])->group(function (): void {
        Route::post('events', [EventController::class, 'store']);
        Route::put('events/{event}', [EventController::class, 'update']);
        Route::post('events/{event}/duplicate', [EventController::class, 'duplicate']);
        Route::delete('events/{event}', [EventController::class, 'destroy']);
        Route::post('events/{event}/status', [EventController::class, 'transitionStatus']);
    });
});
