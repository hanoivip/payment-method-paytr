<?php

use Illuminate\Support\Facades\Route;

Route::any('/paytr/{id}/callback', '\Hanoivip\PaymentMethodPaytr\Callback@notify')->name('paytr.callback');