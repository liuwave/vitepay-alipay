<?php

namespace vitepay\alipay\gateway;

use vitepay\alipay\BaseGateway;
use vitepay\core\entity\PurchaseResponse;
use vitepay\core\interfaces\Payable;
use vitepay\alipay\request\TradePagePayRequest;

class Web extends BaseGateway
{

    /**
     * @inheritDoc
     */
    public function purchase(Payable $charge):PurchaseResponse
    {
        $request = $this->createRequest(TradePagePayRequest::class, $charge);

        return new PurchaseResponse($request->getUri(), PurchaseResponse::TYPE_REDIRECT);
    }
}
