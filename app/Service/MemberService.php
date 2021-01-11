<?php

namespace App\Service;

use App\Models\User;
use Illuminate\Support\Facades\Redis;
use App\Lib\WeiXin\WeixinToken;
use App\Service\JwtAuth;

class MemberService
{
    protected $userModel;
    protected $weixinToken;
    protected $response = [];

    public function __construct(User $userModel,WeixinToken $weixinToken)
    {
        $this->userModel = $userModel;
        $this->weixinToken = $weixinToken;
    }

    /**
     * 获取用户基本信息
     * @param $params
     * @return mixed
     */
    public function getUserInfo($params)
    {
        $data = $this->userModel->queryByMobileOne($params['mobile']);
        
        if (!isset($data)){
            return Result(403,'用户不存在!');
        }
        $this->response = $data;
        return $this->response;
    }

    /**
     * 微信登陆
     * @param array $params
     * return void
     */
    public function weixinLogin(array $params ,$weixData = '')
    {
        $code = $params['code'];
        $data = $this->weixinToken->getSessionKey($code);
        $session_key = $data['session_key'];
        $openid = $data['openid'];
        $errCode = $this->weixinToken->decryptData('scf_mp',$session_key,$params['encrypted_data'],$params['iv'],$weixData);

        if ($errCode != 0){
            return Result($errCode,'手机号码获取失败，请重试!');
        }
        $userData = json_decode($weixData,true);
        $mobile = $userData['phoneNumber'];
        return $this->checkUser($mobile,$openid);
    }

    /**
     * 验证用户是否已注册
     * @param string $mobile
     * @param string $openid
     * return void
     */
    protected function checkUser(string $mobile,string $openid)
    {
        $user = $this->userModel->queryByMobileOne($mobile);
        if (empty($user)){
            //未注册则注册
            $user_id = $this->userModel->insertUser($mobile,$openid);
            $user = [
              'user_id' => $user_id, 'mobile' => $mobile ,'openid' => $openid ,'token' => JwtAuth::createToken($user_id)
            ];
        }else{
            //已注册若openid不匹配则更新最新的openid
            if ($openid != $user->openid){
               $this->userModel->updateUserOpenid($user->user_id,$openid);
               $user->openid = $openid;
            }
            $user->token = JwtAuth::createToken($user->user_id);
        }
        $this->response = $user;
        return $this->response;
    }


}
