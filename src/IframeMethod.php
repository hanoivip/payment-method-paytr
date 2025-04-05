<?php

namespace Hanoivip\PaymentMethodPaytr;

use Hanoivip\CurlHelper;
use Hanoivip\Shop\Facades\OrderFacade;
use Illuminate\Support\Facades\Log;
use Exception;
use Hanoivip\Payment\Facades\BalanceFacade;
use Illuminate\Support\Facades\Auth;
use Hanoivip\User\Facades\UserFacade;

/**
 * Ref https://dev.paytr.com/iframe-api
 * @author GameOH
 *
 */
class IframeMethod extends DirectMethod
{
    private function getUserIp() {
        if (request()->hasHeader('CF-Connecting-IP')) {
            return request()->headers->get('CF-Connecting-IP');
        }
        else if (request()->hasHeader('X-Real-IP')) {
            return request()->headers->get('X-Real-IP');
        }
        else {
            return request()->ip();
        }
    }
    
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
        // convert to TL, if need
        if ($currency != 'TL')
        {
            $amount = BalanceFacade::convert($amount, $currency, 'TL');
            $currency = 'TL';
        }
        $user = UserFacade::getUserCredentials($userId);
        
        // request to paytr
        try
        {
            $cfg = $session->getSecureData();
            srand(time());
            $merchant_oid = $trans->trans_id;
            $userIp = $this->getUserIp();
            $no_installment = 0;
            $max_installment = 0;
            $test_mode = $this->isTestMode() ? "1" : "0";
            $email = empty($user->email) ? $user->name . '@no-exists.com' : $user->email;
            $user_basket = base64_encode(json_encode($this->convertCartToBasket($orderDetail->cart)));
            $hash_str = $cfg['merchant_id'] . $userIp . $merchant_oid . $email . $amount . $user_basket . $no_installment . $max_installment . $currency. $test_mode;
            $paytr_token = base64_encode(hash_hmac('sha256',$hash_str.$cfg['merchant_salt'],$cfg['merchant_key'],true));
            $post_vals=[
                'merchant_id'=>$cfg['merchant_id'],
                'user_ip'=>$userIp,
                'merchant_oid'=>$merchant_oid,
                'email'=>$email,
                'payment_amount'=>$amount,
                'paytr_token'=>$paytr_token,
                'user_basket'=>$user_basket,
                'debug_on'=>$test_mode,
                'no_installment'=>$no_installment,
                'max_installment'=>$max_installment,
                'user_name'=> $user->name,
                'user_address'=> 'hidden',
                'user_phone'=> '9033366688',
                'merchant_ok_url'=>route('paytr.success'),
                'merchant_fail_url'=>route('paytr.failure'),
                'timeout_limit'=>15,
                'currency'=>$currency,
                'test_mode'=>$test_mode,
                'lang'=>config('paytr.lang', '')
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
            report($ex);
            Log::error("Paytr step 1 exception: " . $ex->getMessage());
            return new PaytrFailure($trans, __('hanoivip.paytr::paytr.failure.step1-exception'));
        }
    }
    
    private function getUserEmail($userId) {
        $user = UserFacade::getUserCredentials($userId);
        if (empty($user->email)) {
            return $user->name . '@no-exists.com';
        }
        return $user->email;
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