<?php

use App\Http\Controllers\Frontend\Admin\CheckinController;
use App\Http\Controllers\Frontend\Admin\DashboardController;
use App\Http\Controllers\Frontend\Admin\EventController as AdminEventController;
use App\Http\Controllers\Frontend\Admin\LocationController;
use App\Http\Controllers\Frontend\Admin\StatusEditController;
use App\Http\Controllers\Frontend\Admin\TripController;
use App\Http\Controllers\Frontend\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'userrole:5'])->group(function() {
    Route::get('/', [DashboardController::class, 'renderDashboard'])
         ->name('admin.dashboard');

    Route::get('/stats', [DashboardController::class, 'renderStats'])
         ->name('admin.stats');

    Route::prefix('checkin')->group(function() {
        Route::get('/', [CheckinController::class, 'renderStationboard'])
             ->name('admin.stationboard');
        Route::get('/trip/{tripId}', [CheckinController::class, 'renderTrip'])
             ->name('admin.trip');
        Route::post('/checkin', [CheckinController::class, 'checkin'])
             ->name('admin.checkin');
    });

    Route::prefix('users')->group(function() {
        Route::get('/', [UserController::class, 'renderIndex'])
             ->name('admin.users');
        Route::get('/{id}', [UserController::class, 'renderUser'])
             ->name('admin.users.user');
        Route::post('/update-mail', [UserController::class, 'updateMail'])
             ->name('admin.users.update-mail');
    });

    Route::prefix('status')->group(function() {
        Route::get('/', [StatusEditController::class, 'renderMain'])
             ->name('admin.status');
        Route::get('/edit', [StatusEditController::class, 'renderEdit'])
             ->name('admin.status.edit');
        Route::post('/edit', [StatusEditController::class, 'edit']);
    });

    Route::prefix('trip')->group(function() {
        Route::get('/create', [TripController::class, 'renderForm'])
             ->name('admin.trip.create');
        Route::post('/create', [TripController::class, 'createTrip']);

        Route::get('/{id}', [TripController::class, 'renderTrip'])
             ->name('admin.trip.show');
    });

    Route::prefix('events')->group(function() {
        Route::get('/', [AdminEventController::class, 'renderList'])
             ->name('admin.events');
        Route::post('/delete', [AdminEventController::class, 'deleteEvent'])
             ->name('admin.events.delete');

        Route::get('/suggestions', [AdminEventController::class, 'renderSuggestions'])
             ->name('admin.events.suggestions');
        Route::get('/suggestions/accept/{id}', [AdminEventController::class, 'renderSuggestionCreation'])
             ->name('admin.events.suggestions.accept');
        Route::post('/suggestions/deny', [AdminEventController::class, 'denySuggestion'])
             ->name('admin.events.suggestions.deny');
        Route::post('/suggestions/accept', [AdminEventController::class, 'acceptSuggestion'])
             ->name('admin.events.suggestions.accept.do');


        Route::get('/create', [AdminEventController::class, 'renderCreate'])
             ->name('admin.events.create');
        Route::post('/create', [AdminEventController::class, 'create']);

        Route::get('/edit/{id}', [AdminEventController::class, 'renderEdit'])
             ->name('admin.events.edit');
        Route::post('/edit/{id}', [AdminEventController::class, 'edit']);
    });
});
