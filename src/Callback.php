<?php

namespace Hanoivip\PaymentMethodPaytr;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Hanoivip\PaymentContract\Facades\PaymentFacade;

class Callback extends BaseController
{
    public function notify(Request $request, $id)
    {
        $config = PaymentFacade::getConfig($id);
        if (empty($config))
        {
            return response('NOK1');
        }
        $merchant_key 	= $config['merchant_key'];
        $merchant_salt	= $config['merchant_salt'];
        $merchant_oid   = $request->input('merchant_oid');
        $status         = $request->input('status');
        $amount         = $request->input('total_amount');
        $hash = $request->input('hash');
        
        $calhash = base64_encode( hash_hmac('sha256', $merchant_oid.$merchant_salt.$status.$amount, $merchant_key, true) );
        
        if( $calhash != $hash )
        {
            return response('NOK2');
        }
        if ($status == 'success')
        {
            // event here
            return response('OK');
        }
        else
        {
            return response('NOK3');
        }
    }
}