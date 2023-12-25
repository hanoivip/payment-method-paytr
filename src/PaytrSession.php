<?php

namespace Hanoivip\PaymentMethodPaytr;

use Hanoivip\PaymentMethodContract\IPaymentSession;
use Illuminate\Support\Facades\Log;

class PaytrSession implements IPaymentSession
{
    private $trans;
    private $card;
    private $config;
    
    public function __construct($trans, $card, $config)
    {
        Log::error(print_r($card, true));
        $this->trans = $trans;
        $this->card = $card;
        $this->config = $config;
    }
    
    public function getSecureData()
    {
        return $this->config;
    }

    public function getGuide()
    {
        return __('hanoivip.paytr::paytr.guide');
    }

    public function getTransId()
    {
        return $this->trans->trans_id;
    }

    public function getData()
    {
        return $this->card;
    }

    
}