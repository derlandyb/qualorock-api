<?php

use App\Presentation\Http\Controllers\Organizer\AuthController;
use App\Presentation\Http\Controllers\Organizer\EventController;
use App\Presentation\Http\Controllers\Organizer\EventInfoRequestController;
use App\Presentation\Http\Controllers\Organizer\OrganizerDataController;
use App\Presentation\Http\Controllers\Organizer\PromoterController;
use App\Presentation\Http\Controllers\Organizer\VenueController;
use App\Presentation\Http\Controllers\SuperAdmin\OrganizerApprovalController;
use App\Presentation\Http\Controllers\SuperAdmin\PlanPricingController;
use Illuminate\Support\Facades\Route;

Route::prefix('super-admin')->group(function (): void {
    Route::get('organizers', [OrganizerApprovalController::class, 'index']);
    Route::post('organizers/{organizer}/approve', [OrganizerApprovalController::class, 'approve']);
    Route::post('organizers/{organizer}/reject', [OrganizerApprovalController::class, 'reject']);

    Route::get('plan-prices', [PlanPricingController::class, 'index']);
    Route::post('plan-prices', [PlanPricingController::class, 'store']);

    Route::post('organizers/{organizer}/delete', [OrganizerDataController::class, 'superAdminDelete']);
});

Route::prefix('organizer')->group(function (): void {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware(['auth:organizer', 'organizer.approved'])->group(function (): void {
        Route::get('events', [EventController::class, 'index']);
        Route::post('events', [EventController::class, 'store']);
        Route::put('events/{event}', [EventController::class, 'update']);
        Route::post('events/{event}/duplicate', [EventController::class, 'duplicate']);
        Route::delete('events/{event}', [EventController::class, 'destroy']);
        Route::post('events/{event}/status', [EventController::class, 'transitionStatus']);
        Route::get('events/{event}/promoters', [PromoterController::class, 'eventPromoters']);
        Route::get('events/{event}/info-requests', [EventInfoRequestController::class, 'index']);
        Route::post('info-requests/{infoRequest}/respond', [EventInfoRequestController::class, 'respond']);

        Route::get('venue', [VenueController::class, 'show']);
        Route::put('venue', [VenueController::class, 'update']);
        Route::get('venue/agenda', [VenueController::class, 'agenda']);
        Route::get('venue/history', [VenueController::class, 'history']);

        Route::get('promoters', [PromoterController::class, 'index']);
        Route::post('promoters', [PromoterController::class, 'store']);
        Route::put('promoters/{promoter}', [PromoterController::class, 'update']);
        Route::delete('promoters/{promoter}', [PromoterController::class, 'destroy']);
        Route::post('promoters/{promoter}/events/{event}', [PromoterController::class, 'link']);
        Route::delete('promoters/{promoter}/events/{event}', [PromoterController::class, 'unlink']);

        Route::post('data-export', [OrganizerDataController::class, 'export']);
        Route::get('data-export/{dataExport}', [OrganizerDataController::class, 'showExport']);
        Route::post('account/delete', [OrganizerDataController::class, 'delete']);
    });
});
