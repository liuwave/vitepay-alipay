<?php

namespace vitepay\alipay\gateway;

use vitepay\alipay\BaseGateway;
use vitepay\core\entity\PurchaseResponse;
use vitepay\core\interfaces\Payable;
use vitepay\alipay\request\TradeWapPayRequest;

/**
 * 手机网站支付网关
 * Class Wap
 * @package yunwuxin\pay\channel\alipay\gateway
 */
class Wap extends BaseGateway
{

    /**
     * 购买
     * @param Payable $charge
     * @return PurchaseResponse
     */
    public function purchase(Payable $charge):PurchaseResponse
    {
        $request = $this->createRequest(TradeWapPayRequest::class, $charge);

        return new PurchaseResponse($request->getUri(), PurchaseResponse::TYPE_REDIRECT);
    }
}
