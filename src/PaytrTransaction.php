<?php

namespace Hanoivip\PaymentMethodPaytr;

use Illuminate\Database\Eloquent\Model;

class PaytrTransaction extends Model
{
    public $timestamps = false;
    
    function transaction()
    {
        return $this->hasOne('Hanoivip\Payment\Models\Transaction', 'trans_id', 'trans');
    }
    
}
