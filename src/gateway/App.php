<?php

namespace vitepay\alipay\gateway;

use vitepay\alipay\BaseGateway;
use vitepay\core\entity\PurchaseResponse;
use vitepay\core\interfaces\Payable;
use vitepay\alipay\request\TradeAppPayRequest;

class App extends BaseGateway
{
    
    /**
     * 购买
     *
     * @param Payable $charge
     *
     * @return PurchaseResponse
     */
    public function purchase(Payable $charge):PurchaseResponse
    {
        $request = $this->createRequest(TradeAppPayRequest::class, $charge);
        
        return new PurchaseResponse(parse_url($request->getUri(), PHP_URL_QUERY), PurchaseResponse::TYPE_PARAMS);
    }
}
