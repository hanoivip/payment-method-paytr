<?php

namespace Hanoivip\PaymentMethodPaytr;

use Hanoivip\Events\Payment\TransactionUpdated;
use Hanoivip\Events\Gate\UserTopup;
use Hanoivip\Shop\Facades\OrderFacade;

trait SuccessTrait {
    function onSuccess($transId, $amount) {
        $realAmount = $amount / 100;
        // save
        $record = PaytrTransaction::where('trans', $transId)->first();
        $record->amount = $realAmount;
        $record->status = DirectMethod::STATUS_SUCCESS;
        $record->save();
        // event here
        event(new TransactionUpdated($transId));
        // TODO: shop need statistic too
        // TODO: move this to payment
        $order = $record->transaction->order;
        $orderDetail = OrderFacade::detail($order);
        event(new UserTopup($orderDetail->user_id, 0, $realAmount, $transId));
    }
}