<?php

namespace vitepay\alipay\request;

use vitepay\core\interfaces\Payable;

class TradeQueryRequest extends Request
{
    protected $method = 'alipay.trade.query';

    public function __invoke(Payable $payable)
    {
        $this->bizContent = [
            'out_trade_no' => $payable->getTradeNo(),
        ];
    }
}
