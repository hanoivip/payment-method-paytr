<?php

namespace Hanoivip\PaymentMethodPaytr;

use Hanoivip\Shop\Facades\OrderFacade;
use Illuminate\Support\Facades\Log;
use Mervick\CurlHelper;
use Exception;

/**
 * Ref https://dev.paytr.com/iframe-api
 * @author GameOH
 *
 */
class IframeMethod extends DirectMethod
{
    public function request($trans, $params)
    {
        $record = PaytrTransaction::where('trans', $trans->trans_id)->first();
        if (empty($record))
        {
            return new PaytrFailure($trans, __('hanoivip.paytr::paytr.failure.trans-not-exists'));
        }
        $session = $this->getSession($trans->trans_id);
        if (empty($session))
        {
            return new PaytrFailure($trans, __('hanoivip.paytr::paytr.failure.timeout'));
        }
        // order detail
        $orderDetail = OrderFacade::detail($trans->order);
        $amount = intval($orderDetail->price * 100);
        $currency = $orderDetail->currency;
        // request to paytr
        try
        {
            $cfg = $session->getSecureData();
            srand(time());
            $merchant_oid = $trans->trans_id;
            $userIp = '1.1.1.1';
            $no_installment = 0;
            $max_installment = 0;
            $test_mode = $this->isTestMode() ? "1" : "0";
            $user_basket = base64_encode(json_encode($this->convertCartToBasket($orderDetail->cart)));
            $hash_str = $cfg['merchant_id'] . $userIp . $merchant_oid . config('paytr.report_email') . $amount . $user_basket . $no_installment . $max_installment . $currency. $test_mode;
            $paytr_token = base64_encode(hash_hmac('sha256',$hash_str.$cfg['merchant_salt'],$cfg['merchant_key'],true));
            $post_vals=[
                'merchant_id'=>$cfg['merchant_id'],
                'user_ip'=>$userIp,
                'merchant_oid'=>$merchant_oid,
                'email'=>config('paytr.report_email'),
                'payment_amount'=>$amount,
                'paytr_token'=>$paytr_token,
                'user_basket'=>$user_basket,
                'debug_on'=>$test_mode,
                'no_installment'=>$no_installment,
                'max_installment'=>$max_installment,
                'user_name'=>'hidden',
                'user_address'=>'hidden',
                'user_phone'=>'hidden',
                'merchant_ok_url'=>route('paytr.success'),
                'merchant_fail_url'=>route('paytr.failure'),
                'timeout_limit'=>15,
                'currency'=>$currency,
                'test_mode'=>$test_mode
            ];
            $response = CurlHelper::factory(config('paytr.token_url'))
            ->setPostParams($post_vals)
            ->exec();
            Log::error($response['content']);
            // maybe redirect response here
            if ($response['status'] != 200)
            {
                Log::error("Paytr step 1 error. Stauts " . $response['status']);
                return new PaytrFailure($trans, __('hanoivip.paytr::paytr.failure.step1-error'));
            }
            if (empty($response['data']))
            {
                Log::error("Paytr step 1 error. Content " . $response['content']);
                return new PaytrFailure($trans, __('hanoivip.paytr::paytr.failure.step1-error'));
            }
            if ($response['data']['status'] != 'success')
            {
                Log::error("Paytr step 1 error. Failure");
                return new PaytrFailure($trans, __('hanoivip.paytr::paytr.failure.step1-error'));
            }
            // save transaction
            $record->html = $response['data']['token'];
            $record->status = self::STATUS_PENDING;
            $record->save();
            return new PaytrPending($trans, $response['content']);
        }
        catch (Exception $ex)
        {
            Log::error("Paytr step 1 exception: " . $ex->getMessage());
            return new PaytrFailure($trans, __('hanoivip.paytr::paytr.failure.step1-exception'));
        }
    }
    
    public function openPendingPage($trans)
    {
        $record = PaytrTransaction::where('trans', $trans->trans_id)->first();
        $path="https://www.paytr.com/odeme/guvenli/$record->html";
        return response()->redirectTo($path);
    }
    
    public function openPaymentPage($transId, $guide, $session)
    {
        // no need, just start payemnt
        return response()->redirectToRoute('newtopup.do', ['trans' => $transId]);
    }
    
    public function validate($params)
    {
        return [];
    }
}