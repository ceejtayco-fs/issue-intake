<?php

use App\Http\Controllers\Api\IssueController;
use Illuminate\Support\Facades\Route;

Route::prefix('issues')->group(function () {
    Route::get('/', [IssueController::class, 'index'])->name('api.issues.index');
    Route::post('/', [IssueController::class, 'store'])->name('api.issues.store');
    Route::get('{id}', [IssueController::class, 'show'])
        ->whereNumber('id')
        ->name('api.issues.show');
    Route::match(['put', 'patch'], '{id}', [IssueController::class, 'update'])
        ->whereNumber('id')
        ->name('api.issues.update');
    Route::delete('{id}', [IssueController::class, 'destroy'])
        ->whereNumber('id')
        ->name('api.issues.destroy');
    Route::post('{id}/restore', [IssueController::class, 'restore'])
        ->whereNumber('id')
        ->name('api.issues.restore');
    Route::post('{id}/regenerate-summary', [IssueController::class, 'regenerateSummary'])
        ->whereNumber('id')
        ->name('api.issues.regenerate-summary');
});
