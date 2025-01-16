<?php

use App\Http\Controllers\OrganizationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('organization')->group(function () {
    Route::get('/building/{id}/', [OrganizationController::class, 'getOrganizationsInBuilding']);
    Route::get('/activity/{id}/', [OrganizationController::class, 'getOrganizationActivity'])->where('id', '[0-9]+');
    Route::post('/nearby/', [OrganizationController::class, 'getNearbyOrganizations']);
    Route::get('/{id}/', [OrganizationController::class, 'getOrganization'])->where('id', '[0-9]+');
    Route::get('/activity/', [OrganizationController::class, 'getOrganizationActivityName']);
    Route::get('/search-by-name/', [OrganizationController::class, 'getOrganizationSearchByName']);
});
