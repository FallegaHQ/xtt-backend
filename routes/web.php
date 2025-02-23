<?php
use Illuminate\Support\Facades\Route;

Route::any('/{any}', function () {
    return redirect('/api/v1');
})->where('any', '^(?!api).*$');
