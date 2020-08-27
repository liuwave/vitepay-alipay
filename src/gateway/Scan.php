<?php

namespace vitepay\alipay\gateway;

use vitepay\alipay\BaseGateway;
use vitepay\core\entity\PurchaseResponse;
use vitepay\core\interfaces\Payable;
use vitepay\alipay\request\TradePreCreateRequest;

class Scan extends BaseGateway
{
    
    /**
     * 购买
     *
     * @param Payable $charge
     *
     * @return PurchaseResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function purchase(Payable $charge):PurchaseResponse
    {
        $request = $this->createRequest(TradePreCreateRequest::class, $charge);

        $result = $this->sendRequest($request);

        return new PurchaseResponse($result['qr_code'], PurchaseResponse::TYPE_SCAN);
    }
}
