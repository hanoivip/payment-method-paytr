<?php

namespace Hanoivip\PaymentMethodPaytr;

use Hanoivip\PaymentMethodContract\IPaymentResult;

class PaytrPending implements IPaymentResult
{
    public function getCurrency()
    {}

    public function getDetail()
    {}

    public function toArray()
    {}

    public function isPending()
    {}

    public function isFailure()
    {}

    public function getTransId()
    {}

    public function isSuccess()
    {}

    public function getAmount()
    {}

    
    
}