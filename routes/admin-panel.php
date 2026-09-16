<?php

use App\Presentation\Http\Controllers\SuperAdmin\OrganizerApprovalController;
use Illuminate\Support\Facades\Route;

Route::prefix('super-admin')->group(function (): void {
    Route::get('organizers', [OrganizerApprovalController::class, 'index']);
    Route::post('organizers/{organizer}/approve', [OrganizerApprovalController::class, 'approve']);
    Route::post('organizers/{organizer}/reject', [OrganizerApprovalController::class, 'reject']);
});
