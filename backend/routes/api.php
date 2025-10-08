<?php

use App\Http\Controllers\SectionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserGroupController;
use App\Http\Controllers\InviteAdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Invite routes
Route::prefix('invites')->group(function () {
    // Admin routes (protected)
    // Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [InviteAdminController::class, 'viewAll']); // GET /api/invites
        Route::post('/', [InviteAdminController::class, 'store']); // POST /api/invites
        Route::get('/{key}', [InviteAdminController::class, 'view']); // GET /api/invites/{key}
        Route::delete('/{key}', [InviteAdminController::class, 'delete']); // DELETE /api/invites/{key}
    // });
    
    // Public routes (no auth required)
    Route::post('/{key}/accept', [InviteAdminController::class, 'accept']); // POST /api/invites/{key}/accept
});

Route::apiResources([
    'users' => UserController::class,
    'sections' => SectionController::class,
    'types'=>\App\Http\Controllers\TypeSectionController::class,
    'articles'=>\App\Http\Controllers\ArticleController::class,
    'groups'=>UserGroupController::class
]);

