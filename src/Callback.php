<?php

namespace Hanoivip\PaymentMethodPaytr;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller as BaseController;
use Exception;
use Hanoivip\PaymentContract\Facades\PaymentFacade;

class Callback extends BaseController
{
    use SuccessTrait, FailureTrait;
    
    public function success(Request $request)
    {
        Log::error('Paytr got success redirect ' . print_r($request->all(), true));
        return view('hanoivip.paytr::success-page');
    }
    
    public function failure(Request $request)
    {
        Log::error('Paytr got failure redirect ' . print_r($request->all(), true));
        return view('hanoivip.paytr::failure-page');
    }
    
    public function notify(Request $request, $id)
    {
        try
        {
            Log::error('Paytr got a callback ' . print_r($request->all(), true));
            $config = PaymentFacade::getConfig($id);
            if (empty($config))
            {
                return response('OK');
            }
            $merchant_key 	= $config['merchant_key'];
            $merchant_salt	= $config['merchant_salt'];
            $merchant_oid   = $request->input('merchant_oid'); // == mapping transid
            $status         = $request->input('status');
            $amount         = $request->input('total_amount');
            $hash = $request->input('hash');
            
            $calhash = base64_encode( hash_hmac('sha256', $merchant_oid.$merchant_salt.$status.$amount, $merchant_key, true) );
            
            if( $calhash != $hash )
            {
                Log::error('Paytr got invalid hash???');
                return response('OK');
            }
            if ($status == 'success')
            {
                $this->onSuccess($merchant_oid, $amount);
                return response('OK');
            }
            else
            {
                $this->onFailure($merchant_oid);
                return response('OK');
            }
        }
        catch (Exception $ex)
        {
            Log::error("Paytr callback exception: " . $ex->getMessage());
            return response('OK');
        }
    }
}