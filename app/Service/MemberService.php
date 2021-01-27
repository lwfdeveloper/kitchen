<?php

namespace App\Service;

use App\Models\{User,VipCard};
use Illuminate\Support\Facades\Redis;
use App\Lib\WeiXin\WeixinToken;
use App\Service\JwtAuth;
use App\Service\{MenuService,SmsService};

class MemberService
{
    protected $userModel;
    protected $vipCardModel;
    protected $weixinToken;
    protected $smsService;
    protected $response = [];

    public function __construct(User $userModel,VipCard $vipCardModel,WeixinToken $weixinToken,SmsService $smsService)
    {
        $this->userModel = $userModel;
        $this->vipCardModel = $vipCardModel;
        $this->weixinToken = $weixinToken;
        $this->smsService = $smsService;
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
     * 验证用户是否已注册
     * @param string $mobile
     * @param string $openid
     * return void
     */
    public function checkUser(array $params)
    {
        $smsResult = $this->smsService->checkMobileCode($params['mobile'],$params['smscode']);
        if ($smsResult === false){
            return Result(0,'验证码不正确!');
        }
        unset($params['smscode']);
        $openid = $params['weixinopenid'];
        $user = $this->userModel->queryByMobileOne($params['mobile']);
        if (!isset($user)){
            //未注册则注册
            $user_id = $this->userModel->insertUser($params);
            if (empty($user_id)){
                return Result(0,'登录失败，请稍后重试!');
            }
            $user = [
              'user_id' => $user_id, 'constion_id' => 0, 'mobile' => $params['mobile'] ,'weixinopenid' => $openid,'token' => JwtAuth::createToken($user_id)
            ];
        }else{
            //已注册若openid不匹配则更新最新的openid
            if ($openid != $user->weixinopenid){
               $this->userModel->updateUserOpenid($user->user_id,$openid);
               $user->weixinopenid = $openid;
            }
            $user->token = JwtAuth::createToken($user->user_id);
        }
        $this->response = $user;
        return $this->response;
    }



    /**
     * 获取vip充值卡列表
     * @return mixed
     */
    public function getVipCardList()
    {
        $list = $this->vipCardModel->queryByList();
        foreach ($list as $k => $value){
            $value->url = MenuService::imgPath .$value->url;
            $value->id == 1 ? $value->active = true : $value->active = false;
        }
        $this->response = $list;
        return  $this->response;
    }


    /**
     * 获取openid
     * @param array $params
     */
    public function getUserOpenId(array $params)
    {
        $result = $this->weixinToken->getSessionKey($params['code']);
        unset($result['session_key']);
        return $result;
    }


    /**
     * 微信登陆（获取手机号码暂时不用）
     * @param array $params
     * return void
     */
    public function weixLoginNew(array $params ,$weixData = '')
    {
        $code = $params['code'];
        $data = $this->weixinToken->getSessionKey($code);
        $session_key = $data['session_key'];
        $openid = $data['openid'];
        $errCode = $this->weixinToken->decryptData('sgt_mp',$session_key,$params['encrypted_data'],$params['iv'],$weixData);

        if ($errCode != 0){
            return Result($errCode,'手机号码获取失败，请重试!');
        }
        $userData = json_decode($weixData,true);
        $mobile = $userData['phoneNumber'];
        return $this->checkUser($mobile,$openid);
    }
}
