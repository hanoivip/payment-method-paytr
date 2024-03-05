<?php

use Illuminate\Support\Facades\Route;

Route::any('/paytr/{id}/callback', '\Hanoivip\PaymentMethodPaytr\Callback@notify')->name('paytr.callback');
Route::any('/paytr/success', '\Hanoivip\PaymentMethodPaytr\Callback@success')->name('paytr.success');
Route::any('/paytr/failure', '\Hanoivip\PaymentMethodPaytr\Callback@failure')->name('paytr.failure');


Route::middleware([
    'web',
    'admin'
])->namespace('Hanoivip\PaymentMethodPaytr')
->prefix('ecmin')
->group(function () {
    // Module index
    Route::any('/paytr', 'Admin@index')->name('ecmin.paytr');
    // List & filter
    Route::any('/paytr/list', 'Admin@list')->name('ecmin.paytr.list');
    Route::any('/paytr/detail', 'Admin@detail')->name('ecmin.paytr.detail');
});