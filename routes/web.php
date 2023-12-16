<?php

use Illuminate\Support\Facades\Route;

Route::any('/paytr/{id}/callback', 'Callback@notify')->name('paytr.callback');