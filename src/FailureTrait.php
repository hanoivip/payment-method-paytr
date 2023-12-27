<?php

namespace Hanoivip\PaymentMethodPaytr;

trait FailureTrait {
    function onFailure($transId) {
        // save
        $record = PaytrTransaction::where('trans', $transId)->first();
        $record->amount = 0;
        $record->status = DirectMethod::STATUS_FAILURE;
        $record->save();
    }
}