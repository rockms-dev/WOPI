<?php

use Illuminate\Support\Facades\Route;
use MS\Wopi\Http\Controllers\CheckFileInfoController;
use MS\Wopi\Http\Controllers\GetFileController;
use MS\Wopi\Http\Controllers\PutFileController;
use MS\Wopi\Http\Controllers\WopiPostRequestRouter;

Route::group([
    'prefix'     => 'wopi',
    'as' => 'wopi.',
], function () {
    Route::get('files/{file_id}', CheckFileInfoController::class)->name('checkFileInfo');
    Route::get('files/{file_id}/contents', GetFileController::class)->name('getFile');
    Route::post('files/{file_id}/contents', PutFileController::class)->name('putFile');

    Route::post('files/{file_id}', WopiPostRequestRouter::class)->name('post-router');
});
