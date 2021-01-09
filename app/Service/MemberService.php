<?php

namespace App\Service;

use App\Models\User;
use Illuminate\Support\Facades\Redis;

class MemberService
{
    protected $userModel;
    protected $redis;
    protected $response = [];

    public function __construct(User $userModel,Redis $redis)
    {
        $this->userModel = $userModel;
        $this->redis = $redis;
    }
    /**
     * 获取用户基本信息
     * @param $params
     * @return mixed
     */
    public function getUserInfo($params)
    {
        $data = $this->userModel->queryByMobileOne($params['mobile']);
        $redis = $this->redis;
        
        if (!isset($data)){
            return Result(403,'用户不存在!');
        }
        $this->response = $data;
//        var_dump(json_decode(Redis::get('doctor_callback'),true));die;
//        var_dump($redis::get('doctor_callback'));die;
        return $this->response;
    }
}
