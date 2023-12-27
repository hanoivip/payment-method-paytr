<?php

namespace Hanoivip\PaymentMethodPaytr;

use Hanoivip\Events\Payment\TransactionUpdated;

trait SuccessTrait {
    function onSuccess($transId, $amount) {
        // save
        $record = PaytrTransaction::where('trans', $transId)->first();
        $record->amount = $amount;
        $record->status = DirectMethod::STATUS_SUCCESS;
        $record->save();
        // event here
        event(new TransactionUpdated($transId));
    }
}