<?php

namespace Hanoivip\PaymentMethodPaytr;

use Hanoivip\PaymentMethodContract\IPaymentResult;

class PaytrSuccess implements IPaymentResult
{
    /**
     *
     * @var string transaction id
     */
    private $trans;
    
    public function __construct($trans)
    {
        $this->trans = $trans;
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
        return 0;
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
        return $this->trans->trans_id;
    }
    
    public function getCurrency()
    {
        return 'TL';
    }
    
}