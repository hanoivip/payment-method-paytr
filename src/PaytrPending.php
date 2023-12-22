<?php

namespace Hanoivip\PaymentMethodPaytr;

use Hanoivip\PaymentMethodContract\IPaymentResult;

class PaytrPending implements IPaymentResult
{
    /**
     *
     * @var string transaction id
     */
    private $trans;
    
    private $html;
    
    public function __construct($trans, $html)
    {
        $this->trans = $trans;
        $this->html = $html;
    }
    
    public function getDetail()
    {
        return $this->html;
    }
    
    public function isPending()
    {
        return true;
    }
    
    public function isFailure()
    {
        return false;
    }
    
    public function isSuccess()
    {
        return false;
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