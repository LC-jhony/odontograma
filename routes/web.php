<?php

use App\Http\Controllers\OdontogramPdfController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/odontograma/{odontogram}/pdf', [OdontogramPdfController::class, 'show'])
    ->name('odontogram.pdf')
    ->middleware('auth');
