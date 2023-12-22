<?php

namespace Hanoivip\PaymentMethodPaytr;

use Hanoivip\PaymentMethodContract\IPaymentResult;

class PaytrSuccess implements IPaymentResult
{
    /**
     *
     * @var PaytrTransaction id
     */
    private $record;
    
    public function __construct($record)
    {
        $this->record = $record;
    }
    
    public function getDetail()
    {
        return '';
    }
    
    public function isPending()
    {
        return false;
    }
    
    public function isFailure()
    {
        return false;
    }
    
    public function isSuccess()
    {
        return true;
    }
    
    public function getAmount()
    {
        return $this->record->amount;
    }
    
    public function toArray()
    {
        $arr = [];
        $arr['detail'] = $this->getDetail();
        $arr['amount'] = $this->getAmount();
        $arr['isPending'] = $this->isPending();
        $arr['isFailure'] = $this->isFailure();
        $arr['isSuccess'] = $this->isSuccess();
        $arr['trans'] = $this->getTransId();
        $arr['currency'] = $this->getCurrency();
        return $arr;
    }
    
    public function getTransId()
    {
        return $this->record->trans;
    }
    
    public function getCurrency()
    {
        return 'TL';
    }
    
}