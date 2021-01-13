<?php

namespace App\Lib\WeiXin;

use App\Lib\PayInterface;
use Gaoming13\HttpCurl\HttpCurl;
use Symfony\Component\HttpFoundation\Request;
use Log;

class WeixinPay implements PayInterface
{
    public $option = [
        'appid' => '',
        'key' => '',
        'mch_id' => ""
    ];
    protected $callbackUrl = 'http://scf-api.mymealwell.cn/api/v1/charge/charge_callback';

    public function __construct($type = 'scf_mp')
    {
        $payConfig = getWeiXinPayConfig($type);
        $this->option['appid'] = $payConfig['appid'];
        $this->option['key'] = $payConfig['key'];
        $this->option['mch_id'] = $payConfig['mch_id'];
    }

    /**
     * 创建微信订单
     *
     * @param [type] $trade_no
     * @param [type] $reason
     * @param [type] $openid
     * @param [type] $money
     * @param [type] $callback_url
     * @return void
     */
    public function createOrder($trade_no, $reason, $openid, $money)
    {
        $postArr = [
            'appid' => $this->option['appid'],
            'body' => $reason,
            'mch_id' => $this->option['mch_id'],
            'nonce_str' => md5($trade_no),
            'notify_url' => $this->callbackUrl,
            'openid' => $openid,
            'out_trade_no' => $trade_no,
            'spbill_create_ip' => Request::createFromGlobals()->getClientIp(),
            'total_fee' => $money,
            'trade_type' => 'JSAPI',
            'spbill_create_ip' => $this->getClientIp()
        ];

        // 签名
        ksort($postArr);
        $query = urldecode(http_build_query($postArr)) . '&key=' . $this->option['key'];
        $postArr['sign'] = strtoupper(md5($query));

        // 生成xml
        $postData = '<xml>';
        foreach ($postArr as $key => $value) {
            $postData = $postData . "\r\n" . '<' . $key . '>' . $value . '</' . $key . '>';
        }
        $postData = $postData . '</xml>';

        // 发送数据并解析
        list($body) = HttpCurl::request('https://api.mch.weixin.qq.com/pay/unifiedorder', 'POST', $postData);

        $xmlString = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
        $response = json_decode(json_encode($xmlString), true);
        if ($response['return_code'] == 'SUCCESS' && $response['result_code'] == 'SUCCESS') {
            $data = [
                'appId' => $this->option['appid'],
                'timeStamp' => strval(time()),
                'nonceStr' => md5($trade_no),
                'package' => 'prepay_id=' . $response['prepay_id'],
                'signType' => 'MD5'
            ];
            ksort($data);
            $dataQurey = urldecode(http_build_query($data)) . '&key=' . $this->option['key'];
            $data['paySign'] = strtoupper(md5($dataQurey));
            $result = array_merge($data, [
                'data' => $data
            ]);
            return $data;
        } else {
            Log::error($response);
            return $response;
//            return Result(0, '订单创建失败：' . $response['return_msg'], [
//                'error' => $response['return_msg']
//            ]);
        }
    }

    /**
     * 签名验证
     * @param $arr
     */
    public function Sign($arr)
    {
        unset($arr['sign']);
        ksort($arr);
        $query = urldecode(http_build_query($arr)) . '&key=' . $this->option['key'];
        return strtoupper(md5($query));
    }

    /**
     * 获取客户端真实IP地址
     * @return array|false|string
     */
    protected function getClientIp()
    {
        $cip = 'unknown';
        if($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        }
        elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        }
        return $cip;
    }
}