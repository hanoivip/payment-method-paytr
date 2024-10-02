<?php

namespace Hanoivip\PaymentMethodPaytr;

use Carbon\Carbon;
use Hanoivip\CurlHelper;
use Hanoivip\PaymentMethodContract\IPaymentMethod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;
use Hanoivip\PaymentMethodContract\IPaymentSession;
use Hanoivip\Shop\Facades\OrderFacade;
/**
 * Ref https://dev.paytr.com/iframe-api
 * @author GameOH
 *
 */
class DirectMethod implements IPaymentMethod
{
    const MAX_BASKET_SIZE = 3;
    
    const SESSION_TIMEOUT = 15 * 60;
    
    protected $config;
    
    const STATUS_INIT = 0;
    const STATUS_PENDING = 1;
    const STATUS_CANCEL = 2;
    const STATUS_SUCCESS = 3;
    const STATUS_FAILURE = 4;
    
    public function endTrans($trans)
    {}

    public function cancel($trans)
    {}

    /**
     * Lưu transaction
     * Load dữ liệu thẻ (nếu có)
     *  
     */
    public function beginTrans($trans)
    {
        $exists = PaytrTransaction::where('trans', $trans->trans_id)->get();
        if ($exists->isNotEmpty())
            throw new Exception('Paytr transaction already exists');
        $log = new PaytrTransaction();
        $log->trans = $trans->trans_id;
        $log->mapping = $trans->trans_id;
        $log->status = self::STATUS_INIT;
        $log->save();
        $session = new PaytrSession($trans, null, $this->config);
        $this->saveSession($trans->trans_id, $session);
        return $session;
    }
    
    protected function saveSession($transId, $session)
    {
        Cache::put('PAYTR_SESSION_' . $transId, $session, Carbon::now()->addSeconds(self::SESSION_TIMEOUT));
    }
    /**
     * 
     * @param string $transId
     * @return IPaymentSession
     */
    protected function getSession($transId)
    {
        if (Cache::has("PAYTR_SESSION_$transId"))
        {
            return Cache::get("PAYTR_SESSION_$transId");
        }
    }
    /**
     * TODO: maximum cart must be 3
     * @param array $cart
     * @return array
     */
    protected function convertCartToBasket($cart)
    {
        $count = 0;
        $basket = [];
        foreach ($cart->items as $item)
        {
            $basket[] = [$item->title, $item->price, 1];//TODO: make count in cart
            ++$count;
            if ($count>=self::MAX_BASKET_SIZE) break;
        }
        return $basket;
    }

    public function request($trans, $params)
    {
        if (!isset($params['card_owner']) ||
            !isset($params['card_number']) ||
            !isset($params['expiry_month']) ||
            !isset($params['expiry_year']) ||
            !isset($params['cvv']))
        {
            return new PaytrFailure($trans, __('hanoivip.paytr::paytr.failure.missing-params'));
        }
        $savecard = !empty($params['savecard']);
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
        //Log::error(print_r($session, true));
        $orderDetail = OrderFacade::detail($trans->order);
        $amount = $orderDetail->price;
        $currency = $orderDetail->currency;
        // request to paytr
        try
        {
            $cfg = $session->getSecureData();
            srand(time());
            $merchant_oid = $trans->trans_id;
            $userIp = '1.1.1.1';
            $installment_count = 0;
            $test_mode = $this->isTestMode() ? "1" : "0";
            $hash_str = $cfg['merchant_id'] . $userIp . $merchant_oid . config('paytr.report_email') . $amount . 'card' . $installment_count. $currency. $test_mode. "0";
            $token = base64_encode(hash_hmac('sha256',$hash_str.$cfg['merchant_salt'],$cfg['merchant_key'],true));
            $ppp = [
                'cc_owner' => $params['card_owner'],
                'card_number' => $params['card_number'],
                'expiry_month' => $params['expiry_month'],
                'expiry_year' => $params['expiry_year'],
                'cvv' => $params['cvv'],
                'merchant_id' => $cfg['merchant_id'],
                'user_ip' => $userIp,
                'merchant_oid' => $merchant_oid,
                'email' => config('paytr.report_email'),
                'payment_type' => 'card',
                'payment_amount' => $amount,
                'currency' => $currency,
                'test_mode' => $test_mode,
                'non_3d' => '0',
                'merchant_ok_url' => route('paytr.success'),
                'merchant_fail_url' => route('paytr.failure'),
                'user_name' => 'Not your business',
                'user_address' => 'Not your business',
                'user_phone' => '05555555555',
                'user_basket' => json_encode($this->convertCartToBasket($orderDetail->cart)),
                'debug_on' => $test_mode,
                'client_lang' => 'en',
                'paytr_token' => $token,
                'non3d_test_failed' => '0',
                'installment_count' => $installment_count, 
            ];
            $response = CurlHelper::factory(config('paytr.api_url'))
            ->setPostParams($ppp)
            ->exec();
            // maybe redirect response here
            if ($response['status'] != 200)
            {
                Log::error("Paytr step 1 error. Stauts " . $response['status']  . ' Content ' . $response['content']);
                return new PaytrFailure($trans, __('hanoivip.paytr::paytr.failure.step1-error'));
            }
            //Log::debug(print_r($response['content'], true));
            /*
            if (!empty($response['data']))
            {
                if ($response['data']['status'] == 'failed')
                {
                    return new PaytrFailure($trans, $response['data']['reason']);
                }
                if ($response['data']['status'] == 'wait_callback')
                {
                    return new PaytrPending($trans);
                }
            }*/
            if ($savecard)
            {
                //Log::error(print_r($params, true));
                $card = PaytrCard::where('user_id', $orderDetail->user_id)->first();
                if (empty($card))
                {
                    $card = new PaytrCard();
                }
                $card->user_id = $orderDetail->user_id;
                $card->owner = $params['card_owner'];
                $card->number = $params['card_number'];
                $card->expire_month = $params['expiry_month'];
                $card->expire_year = $params['expiry_year'];
                $card->cvv = $params['cvv'];
                $card->save();
            }
            // save transaction
            $record->html = $response['content'];
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
    
    public function isTestMode()
    {
        return config('paytr.test_mode', false);
    }

    public function query($trans, $force = false)
    {
        $record = PaytrTransaction::where('trans', $trans->trans_id)->first();
        switch ($record->status)
        {
            case self::STATUS_CANCEL:
                return new PaytrFailure($trans, "cancel");
            case self::STATUS_PENDING:
                return new PaytrPending($trans, $record->html);
            case self::STATUS_FAILURE:
                return new PaytrFailure($trans, "fail");
            case self::STATUS_SUCCESS:
                return new PaytrSuccess($record);
            default:
                return new PaytrFailure($trans, "invalid");
        }
    }

    public function config($cfg)
    {
        $this->config = $cfg;
    }

    public function validate($params)
    {
        $errors = [];
        if (!isset($params['card_owner']))
        {
            $errors['card_owner'] = 'Card owner must be filled';
        }
        if (!isset($params['card_number']))
        {
            $errors['card_number'] = 'Card number must be filled';
        }
        if (!isset($params['expiry_month']))
        {
            $errors['expiry_month'] = 'Expire month must be filled';
        }
        if (!isset($params['expiry_year']))
        {
            $errors['expiry_year'] = 'Expire year must be filled';
        }
        if (!isset($params['cvv']))
        {
            $errors['cvv'] = 'CVV must be filled';
        }
        return $errors;
    }
    
    public function openPendingPage($trans)
    {
        $record = PaytrTransaction::where('trans', $trans->trans_id)->first();
        return view('hanoivip.paytr::pending-page', ['detail' => $record->html]);
    }
    
    public function openPaymentPage($transId, $guide, $session)
    {
        return view('hanoivip.paytr::payment-page', ['trans' => $transId, 'guide' => $guide, 'data' => $session]);
    }
}