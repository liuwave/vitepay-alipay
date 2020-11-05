<?php

namespace vitepay\alipay;

use Carbon\Carbon;
use DomainException;
use http\Exception\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use think\Cache;
use think\facade\Log;
use think\helper\Str;
use think\Request;
use vitepay\core\entity\PurchaseResponse;
use vitepay\core\Gateway;
use vitepay\core\entity\PurchaseResult;
use vitepay\core\interfaces\Payable;
use vitepay\core\interfaces\Refundable;

use vitepay\alipay\request\TradeQueryRequest;
use vitepay\alipay\request\TradeRefundQueryRequest;
use vitepay\alipay\request\TradeRefundRequest;

use function vitepay\alipay\convert_key;

/**
 * Class BaseGateway
 * @package vitepay\channel
 */
class BaseGateway extends Gateway
{
    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     *
     * @return mixed|void
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('sign_type', 'RSA2');
        $resolver->setRequired(['app_id', 'alipay_public_key', 'app_private_key']);
        
        $resolver->setNormalizer(
          'alipay_public_key',
          function (Options $options, $value) {
              if (is_file($value)) {
                  $value = file_get_contents($value);
              }
              
              return $value;
          }
        );
        
        $resolver->setNormalizer(
          'app_private_key',
          function (Options $options, $value) {
              if (is_file($value)) {
                  $value = file_get_contents($value);
              }
              
              return $value;
          }
        );
    }
    
    /**
     * 订单查询
     *
     * @param Payable $charge
     *
     * @return PurchaseResult
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function query(Payable $charge)
    {
        $request = $this->createRequest(TradeQueryRequest::class, $charge);
        
        $data = $this->sendRequest($request);
        
        return new PurchaseResult(
          $this->getName(),
          $data[ 'trade_no' ],
          $data[ 'total_amount' ] * 100,
          'TRADE_SUCCESS' == $data[ 'trade_status' ],
          Carbon::now(),
          $data
        );
    }
    
    /**
     * 退款
     *
     * @param Refundable $refund
     *
     * @return array
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function refund(Refundable $refund) : array
    {
        $request = $this->createRequest(TradeRefundRequest::class, $refund);
        
        return $this->sendRequest($request);
    }
    
    /**
     * @param \vitepay\core\interfaces\Refundable $refund
     *
     * @return array
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function refundQuery(Refundable $refund) : array
    {
        $request = $this->createRequest(TradeRefundQueryRequest::class, $refund);
        
        return $this->sendRequest($request);
    }
    
    /**
     * @param \think\Request $request
     *
     * @return mixed|\think\Response
     */
    public function completePurchase(Request $request)
    {
        $data = $request->post('', null, null);
        
        $sign = $data[ 'sign' ];
        
        unset($data[ 'sign' ], $data[ 'sign_type' ]);
        
        if ($this->isLog()) {
            Log::info(
              'alipay:Notice'.
              json_encode([$data, $sign, $this->buildSignContent($data)], JSON_UNESCAPED_SLASHES && JSON_UNESCAPED_UNICODE)
            );
        }
        
        $this->verifySign($this->buildSignContent($data), $sign);
        
        $charge = $this->retrieveCharge($data[ 'out_trade_no' ]);
        if (!$charge->isComplete()) {
            $charge->onComplete(
              new PurchaseResult(
                $this->getName(),
                $data[ 'trade_no' ],
                $data[ 'total_amount' ] * 100,
                'TRADE_SUCCESS' == $data[ 'trade_status' ],
                !empty($data[ 'gmt_payment' ]) ? Carbon::parse($data[ 'gmt_payment' ]) : null,
                $data
              )
            );
        }
        
        return response('success');
    }
    
    /**
     * @param $params
     *
     * @return string
     */
    protected function buildSignContent($params)
    {
        ksort($params);
        
        return urldecode(http_build_query($params));
    }
    
    /**
     * @param $data
     * @param $sign
     *
     * @return mixed|void
     */
    public function verifySign($data, $sign)
    {
        if ($this->isLog()) {
            Log::info('alipay:verifySign'.$data);
        }
        $key = convert_key($this->getOption('alipay_public_key'), 'public key');
        if ('RSA2' == $this->getOption('sign_type')) {
            $result = (bool)openssl_verify($data, base64_decode($sign), $key, OPENSSL_ALGO_SHA256);
        }
        else {
            $result = (bool)openssl_verify($data, base64_decode($sign), $key);
        }
        if (!$result) {
            throw new DomainException('签名验证失败');
        }
    }
    
    /**
     * @param array $params
     *
     * @return string
     */
    public function generateSign(array $params) : string
    {
        $data = $this->buildSignContent($params);
        
        if ($this->isLog()) {
            Log::info('alipay:generateSign:'.$data);
        }
        
        $key = convert_key($this->getOption('app_private_key'), 'RSA PRIVATE key');
        if ("RSA2" == $params[ 'sign_type' ]) {
            openssl_sign($data, $sign, $key, OPENSSL_ALGO_SHA256);
        }
        else {
            openssl_sign($data, $sign, $key);
        }
        
        return base64_encode($sign);
    }
    
    /**
     * @param \Psr\Http\Message\RequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return array
     */
    protected function handleResponse(RequestInterface $request, ResponseInterface $response) : array
    {
        $uri   = $request->getUri();
        $query = parse_url($uri, PHP_URL_QUERY);
        parse_str($query, $body);
        
        $method = $body[ 'method' ];
        
        $content = $response->getBody()
          ->getContents();
        
        $response = json_decode($content, true);
        
        if ($response === null) {
            //echo json_last_error_msg();
            throw new \RuntimeException(json_last_error_msg());
        }
        if ($this->isLog()) {
            Log::info('alipay:response'.json_encode([$response], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES));
        }
        $key = str_replace('.', '_', $method).'_response';
        if (!isset($response[ $key ])) {
            throw new \RuntimeException('系统繁忙');
        }
        
        $result = $response[ $key ];
        
        if (empty($result[ 'code' ]) || $result[ 'code' ] != 10000) {
            throw new DomainException(
              htmlspecialchars_decode(isset($result[ 'sub_msg' ]) ? $result[ 'sub_msg' ] : $result[ 'msg' ])
            );
        }
        
        $this->verifySign(json_encode($result, JSON_UNESCAPED_UNICODE), $response[ 'sign' ] ?? '');
        
        return $result;
    }
    
    /**
     * @inheritDoc
     */
    public function purchase(Payable $charge) : PurchaseResponse
    {
        throw new InvalidArgumentException('Channel [wechat] has no gateway');
    }
}
