<?php

use App\Http\Controllers\Web\IssueWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('issues.index'));

Route::prefix('issues')->name('issues.')->group(function () {
    Route::get('/', [IssueWebController::class, 'index'])->name('index');
    Route::get('create', [IssueWebController::class, 'create'])->name('create');
    Route::post('/', [IssueWebController::class, 'store'])->name('store');
    Route::get('{id}', [IssueWebController::class, 'show'])->whereNumber('id')->name('show');
    Route::match(['put', 'patch'], '{id}', [IssueWebController::class, 'update'])->whereNumber('id')->name('update');
    Route::delete('{id}', [IssueWebController::class, 'destroy'])->whereNumber('id')->name('destroy');
    Route::post('{id}/restore', [IssueWebController::class, 'restore'])->whereNumber('id')->name('restore');
    Route::post('{id}/regenerate-summary', [IssueWebController::class, 'regenerate'])->whereNumber('id')->name('regenerate');
});
