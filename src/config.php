<?php
/**
 * Created by PhpStorm.
 * User: liuwave
 * Date: 2020/8/26 19:11
 * Description:
 */

return [
  'sandbox'     => true,//沙箱模式
  'type'        => '',//默认为 \vitepay\alipay\BaseGateway
  'credentials' => [
    'app_id'            => '',
    'alipay_public_key' => '', //支付宝公钥
    'app_private_key'   => '',//应用私钥
  ],
  "gateways"    => [
    "app"  => [
      'type'    => 'app',//对应\vitepay\alipay\gateway\App
      'sandbox' => true,
    ],
    "scan" => [
      'sandbox' => true,
      'type'    => 'scan',//对应\vitepay\alipay\gateway\Scan
    ],
    "wap"  => [
      'sandbox' => true,
      'type'    => 'wap',//对应\vitepay\alipay\gateway\Wap
    ],
    "web"  => [
      'sandbox' => true,
      'type'    => 'wap',//对应\vitepay\alipay\gateway\Web
    ],
  ],

];