# vitepay-alipay


vitepay的支付宝支付网关。


## 安装

    composer require vitepay/core vitepay/alipay
    


## 配置

修改配置`config/vitepay_alipay.php`


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
	
## 使用

参见 [liuwave/vitepay](https://github.com/liuwave/vitepay)


## 相关支付

- [liuwave/vitepay-wechat](https://github.com/liuwave/vitepay-wechat)



## License
    

The MIT License (MIT). Please see [License File](https://choosealicense.com/licenses/mit) for more information.
    
