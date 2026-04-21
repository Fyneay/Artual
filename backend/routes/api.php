<?php

use App\Http\Controllers\SectionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserGroupController;
use App\Http\Controllers\InviteAdminController;
use App\Http\Controllers\FileCheckController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\ListPeriodController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});


Route::prefix('invites')->middleware('auth:sanctum')->group(function () {
        Route::get('/', [InviteAdminController::class, 'viewAll']);
        Route::post('/', [InviteAdminController::class, 'store']);
        Route::get('/{key}', [InviteAdminController::class, 'view']);
        Route::delete('/{key}', [InviteAdminController::class, 'delete']);

    Route::post('/{key}/accept', [InviteAdminController::class, 'accept']);
});

Route::get('/sections/types', [SectionController::class, 'getSectionsByType'])->middleware('auth:sanctum');
Route::get('/sections/{id}/opis', [SectionController::class, 'generateOpis'])->middleware('auth:sanctum');
Route::get('/articles/section/{sectionId}', [\App\Http\Controllers\ArticleController::class, 'getBySection'])->middleware('auth:sanctum');
Route::get('/articles/section/{sectionId}/statistics', [\App\Http\Controllers\ArticleController::class, 'getSectionStatistics'])->middleware('auth:sanctum');
Route::get('/articles/ready-for-destruction', [\App\Http\Controllers\ArticleController::class, 'getReadyForDestruction'])->middleware('auth:sanctum');
Route::get('/article-files/{fileId}/download', [\App\Http\Controllers\ArticleController::class, 'downloadFile'])->middleware('auth:sanctum');
Route::get('/list-periods', [ListPeriodController::class, 'index'])->middleware('auth:sanctum');
Route::get('/types-document', [\App\Http\Controllers\TypeDocumentController::class, 'index'])->middleware('auth:sanctum');

Route::group(['middleware'=>'auth:sanctum'], function () {
    Route::apiResources([
        'users' => UserController::class,
        'sections' => SectionController::class,
        'types'=>\App\Http\Controllers\TypeSectionController::class,
        'articles'=>\App\Http\Controllers\ArticleController::class,
        'groups'=>UserGroupController::class,
        'destructions'=>\App\Http\Controllers\DestructionController::class,
        'exchanges'=>\App\Http\Controllers\ExchangeController::class,
        'access'=>AccessController::class,
    ]);
});

Route::prefix('destructions')->middleware('auth:sanctum')->group(function () {
    Route::get('/{id}/download', [\App\Http\Controllers\DestructionController::class, 'downloadAct']);
    Route::post('/{id}/destroy-articles', [\App\Http\Controllers\DestructionController::class, 'destroyArticles']);
});

Route::prefix('exchanges')->middleware('auth:sanctum')->group(function () {
    Route::get('/{id}/download', [\App\Http\Controllers\ExchangeController::class, 'downloadAct']);
    Route::post('/{id}/transfer-articles', [\App\Http\Controllers\ExchangeController::class, 'transferArticles']);
});

Route::prefix('file-check')->middleware('auth:sanctum')->group(function () {
    Route::post('/upload', [FileCheckController::class, 'uploadAndCheck']); // POST /api/file-check/upload
    Route::get('/status/{path}', [FileCheckController::class, 'checkStatus'])->where('path', '.*');
    Route::delete('/quarantine/{path}', [FileCheckController::class, 'deleteFromQuarantine'])->where('path', '.*');
});

Route::prefix('signatures')->middleware('auth:sanctum')->group(function () {
    Route::post('/', [\App\Http\Controllers\SignatureController::class, 'store']);
    Route::get('/article/{articleId}/signing-data', [\App\Http\Controllers\SignatureController::class, 'getSigningData']);
    Route::get('/article/{articleId}/archives', [\App\Http\Controllers\SignatureController::class, 'showArchivesByArticle']);
    Route::post('/archive', [\App\Http\Controllers\SignatureController::class, 'createArchive']);
    Route::get('/archive/{archiveId}/download', [\App\Http\Controllers\SignatureController::class, 'downloadArchive']);
    Route::get('/{id}', [\App\Http\Controllers\SignatureController::class, 'show']);
});
Route::prefix('access')->middleware('auth:sanctum')->group(function () {
    Route::get('/article/{articleId}', [AccessController::class, 'getByArticle']);
    Route::get('/my', [AccessController::class, 'getMyAccesses']);
});

Route::get('/status', [\App\Http\Controllers\StatusController::class, 'index'])->middleware('auth:sanctum');

