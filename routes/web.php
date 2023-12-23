<?php

use Illuminate\Support\Facades\Route;

Route::any('/paytr/{id}/callback', '\Hanoivip\PaymentMethodPaytr\Callback@notify')->name('paytr.callback');
Route::any('/paytr/success', '\Hanoivip\PaymentMethodPaytr\Callback@success')->name('paytr.success');
Route::any('/paytr/failure', '\Hanoivip\PaymentMethodPaytr\Callback@failure')->name('paytr.failure');