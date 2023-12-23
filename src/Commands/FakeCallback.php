<?php

namespace Hanoivip\PaymentMethodPaytr\Commands;

use Illuminate\Console\Command;
use Hanoivip\PaymentMethodPaytr\SuccessTrait;

class FakeCallback extends Command
{
    use SuccessTrait;
    
    protected $signature = 'paytr:callback {trans} {amount}';
    
    protected $description = 'Fake paytr callback';
    
    public function handle()
    {
        $transId = $this->argument('trans');
        $amount = $this->argument('amount');
        $this->onSuccess($transId, $amount);
    }
}
