<?php

namespace Hanoivip\PaymentMethodPaytr;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Admin extends BaseController
{
    public function index()
    {
        return redirect()->route('ecmin.paytr.list');
    }
    
    public function list(Request $request)
    {
        $order = null;
        if ($request->getMethod() == 'POST')
        {
            $order = $request->input('order');
        }
        $records = null;
        if (empty($order))
        {
            $records = PaytrTransaction::paginate(20);
        }
        else 
        {
            $records = PaytrTransaction::where('trans', $order)->paginate(20);
        }
        return view('hanoivip.paytr::admin.list', ['records' => $records]);
    }
    
}