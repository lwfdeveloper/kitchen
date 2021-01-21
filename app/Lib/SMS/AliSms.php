<?php

namespace App\Lib\SMS;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use App\Lib\SmsInterface;
use Illuminate\Support\Facades\Redis;

class AliSms implements SmsInterface
{
    protected $config = [
        'accessKeyId' => '' ,
        'accessSecret' => ''
    ];

    /**
     * 短信模板使用场景
    */
    protected $codeType = [
        'login' => ''
    ];

    public function __construct()
    {
        $this->config['accessKeyId'] = env('AliSMSACCESSKEY','');
        $this->config['accessSecret'] = env('AliSMSSECRET','');
        $this->codeType['login'] = env('AliSMSCODETYPE','');
    }

    /**
     * 初始化短信配置信息
     * @return mixed
     */
    private function AsDefaultClient()
    {
        return AlibabaCloud::accessKeyClient($this->config['accessKeyId'], $this->config['accessSecret'])
            ->regionId('cn-hangzhou')
            ->asDefaultClient();
    }

    /**
     * 发送验证码调用阿里接口
     * @param $phone
     * @param string $code_type
     * @param $code
     * @return mixed
     */
    private function SendSms($phone, $code_type = '1', $code)
    {
        $client = $this->AsDefaultClient();
        try {
            $code_type = $this->codeType['login'];
            $code = json_encode(['code' => $code]);

            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'PhoneNumbers' => $phone,
                        'SignName' => "膳贡平台",
                        'TemplateCode' => $code_type,
                        'TemplateParam' => $code,
                    ],
                ])
                ->request();
            return $result->toArray();
        } catch (ClientException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        } catch (ClientException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        }
    }

    /**
     * 发送阿里云短信验证码
     * @param $mobile
     * @param string $type
     */
    public  function Send($mobile ,$type = 'login')
    {
        $cacheKey = "sended:$mobile:$type";
        $sendedCache = Redis::get($cacheKey);
        $sendedTotal = 0;
        if ($sendedCache === false) {
            $sendedTotal = 0;
        } else if ($sendedCache >= 10) {
            return Result(0, '您今天短信发送过于频繁，请稍后再试！');
        } else {
            $sendedTotal = $sendedCache;
        }
        // 发送短信
        $randToken = (string)rand(1000, 9999);

//        $key = $telphone.'_'.$randToken;
//        $redis->hset("SMS:{$key}",$telphone,$randToken);
//        $redis->Expire("SMS:{$key}",3000);
//        $result = (new \AliCloud\AliSMS())->SendSms($mobile, $type, $randToken);
        $result = $this->SendSms($mobile, $type ,$randToken);
        if($result['Code'] == 'OK') {
            Redis::set($cacheKey, ++$sendedTotal, 1800);
            return ['token' => $randToken, 'return_result' => '阿里短信发送成功'];
        } else if($result['Code'] == 'isv.DAY_LIMIT_CONTROL' || $result['Code'] == 'isv.BLACK_KEY_CONTROL_LIMIT') {
            return ['token' => '','return_result' => json_encode(['phone' => $mobile, 'res' => $result['Code']])];
        } else {
            return ['token' => '','return_result' => json_encode(['phone' => $mobile, 'res' => $result['Code'] ?? '系统出错!'])];
        }

    }
}