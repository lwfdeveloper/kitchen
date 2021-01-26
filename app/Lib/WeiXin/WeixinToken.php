<?php

namespace App\Lib\WeiXin;

use App\Lib\TokenInterface;
use Gaoming13\HttpCurl\HttpCurl;
use Illuminate\Support\Facades\Redis;

class WeixinToken implements TokenInterface
{

    /**
     * 微信sessionKey
     * @var
     */
    protected $sessionKey;
    protected $appid;
    public static $OK = 0;
    public static $IllegalAesKey = -41001;
    public static $IllegalIv = -41002;
    public static $IllegalBuffer = -41003;
    public static $DecodeBase64Error = -41004;

    /**
     * 获取微信access token
     * @return string
     */
    public function getToken($type = 'sgt_mp')
    {
        $appidData = getConfigAppid($type);
        $appid = $appidData['appid'];
        $secret = $appidData['secret'];
        if (Redis::get('weixin_access' . $type) == null) {
            list($body) = HttpCurl::request('https://api.weixin.qq.com/cgi-bin/token', 'get', [
                'grant_type' => 'client_credential',
                'appid' => $appid,
                'secret' => $secret
            ]);
            $access = json_decode($body, true);
            Redis::setex('weixin_access' . $type,  120,$access['access_token']);
            return Redis::get('weixin_access' . $type);
        } else {
            return Redis::get('weixin_access' . $type);
        }
    }


    /**
     * 获取jsapi_ticket
     * @return string
     */
    public function GetJsapiTicket($type)
    {
        if (Redis::get('weixin_jsticket' . $type) == null) {
            list($body) = HttpCurl::request('https://api.weixin.qq.com/cgi-bin/ticket/getticket', 'get', [
                'access_token' => $this->getToken($type),
                'type' => 'jsapi'
            ]);
            $access = json_decode($body, true);
            Redis::setex('weixin_jsticket' . $type, 7100,$access['ticket']);
            return Redis::get('weixin_jsticket' . $type);
        } else {
            return Redis::get('weixin_jsticket' . $type);
        }
    }


    /**
     * 获取微信sessionKey以及openId
     * @param string $code
     */
    public function getSessionKey($code = '',$type = 'sgt_mp')
    {
        if (empty($code)) return Result(0,'code不存在!');
        $appidData = getConfigAppid($type);
        $appid = $appidData['appid'];
        $secret = $appidData['secret'];

        list($body) = HttpCurl::request('https://api.weixin.qq.com/sns/jscode2session', 'get', [
            'appid' => $appid,
            'secret' => $secret,
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ]);
        $data = json_decode($body, true);

        if (!array_key_exists('openid',$data)){
            return Result(0,'微信授权失败,请重新操作!');
        }
        //绑定微信开发平台才有unionid
        if (!isset($data['unionid'])){
            $data['unionid'] = '';
        }
        return $data;
    }


    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $type string 小程序配置类型
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData($type = 'sgt_mp', $sessionKey,$encryptedData, $iv, &$data )
    {
        $appidData = getConfigAppid($type);
        $this->appid = $appidData['appid'];
        $this->sessionKey = $sessionKey;

        if (strlen($this->sessionKey) != 24) {
            return self::$IllegalAesKey;
        }
        $aesKey=base64_decode($this->sessionKey);

        if (strlen($iv) != 24) {
            return self::$IllegalIv;
        }

        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result = openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj = json_decode( $result );

        if( $dataObj  == NULL ) {
            return self::$IllegalBuffer;
        }
        if( $dataObj->watermark->appid != $this->appid ) {
            return self::$IllegalBuffer;
        }
        $data = $result;
        return self::$OK;
    }
}
