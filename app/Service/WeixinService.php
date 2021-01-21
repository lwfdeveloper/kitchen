<?php

namespace App\Service;

use Illuminate\Support\Facades\Redis;
use App\Models\User as UserModel;
use App\Lib\AliCloud\AliOss;
use App\Lib\WeiXin\WeixinToken;
use Gaoming13\HttpCurl\HttpCurl;

class WeixinService
{
    protected $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * 创建微信小程序码　
     * @param array $params
     */
    public function createWeixinQrcode(array $params)
    {
        $user = $this->userModel->queryByIdOne($params['user_id']);

        if (!isset($user)){
            return Result(0,'该用户不存在!');
        }
        $data = $params;
        unset($data['user_id']);

        $accessToken = (new WeixinToken)->getToken();
        list($wx_code) = HttpCurl::request(
            'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$accessToken,
            'post',
            json_encode($data)
        );
        $result = json_decode($wx_code,true);

        if ($result['errcode'] == 25009 || $result['errcode'] == 41030){
            return Result($result['errcode'],'生成小程序码失败');
        }

        $path = base_path()."/public/weixin_mp/qrcode";
        //图片名字
        $imageName = "25220_".date("His",time())."_".rand(1111,9999).'.png';
        if (!is_dir($path)){ //判断目录是否存在 不存在就创建 ,注意权限问题
            mkdir($path,0777,true);
        }
        $imageSrc= $path."/". $imageName; //完整图片名字
        $result = file_put_contents($imageSrc,$wx_code);
        $aliOss = new AliOss;
        $result = $aliOss::SaveWeixinQrcodeFile($imageSrc,'sgt_mp',$params['user_id'] );
        unlink($imageSrc);

        return $result;
    }

}