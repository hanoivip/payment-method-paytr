<?php

namespace Hanoivip\PaymentMethodPaytr;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller as BaseController;
use Hanoivip\PaymentContract\Facades\PaymentFacade;
use Hanoivip\Events\Payment\TransactionUpdated;

class Callback extends BaseController
{
    public function notify(Request $request, $id)
    {
        Log::error('Paytr got a callback ' . print_r($request->all(), true));
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
            Log::error('Paytr got invalid hash???');
            return response('NOK2');
        }
        if ($status == 'success')
        {
            // save
            $record = PaytrTransaction::where('trans', $merchant_oid)->first();
            if (empty($record))
            {
                Log::error('Paytr got invalid transaction ID with success result???' . $merchant_oid);
                return response('NOK4');
            }
            $record->amount = $amount;
            $record->status = PaytrMethod::STATUS_SUCCESS;
            $record->save();
            // event here
            event(new TransactionUpdated($merchant_oid));
            return response('OK');
        }
        else
        {
            //Log::error('Paytr got failure result!');
            return response('NOK3');
        }
    }
}