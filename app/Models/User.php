<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'member';
    protected $password = '123456';

    /**
     * 根据用户id获取信息
     * @param $id
     * @return array
     */
    public function queryByIdOne($id)
    {
        $user = DB::table('member')->select('id as user_id','mobile','openid')->where('id', $id)->first();
        return $user;
    }

    /**
     * 根据手机号码获取用户信息
     * @param $mobile
     * @return array
     */
    public function queryByMobileOne($mobile)
    {
        $user = DB::table('member')->select('id as user_id','mobile','openid')->where('mobile', $mobile)->first();
        return $user;
    }

    /**
     * 根据手机号码新增用户
     * @param string $mobile
     * @param string $openid
     * return id int
     */
    public function insertUser(string $mobile,string $openid)
    {
        $id = DB::table('member')->insertGetId([
            'mobile' => $mobile,
            'openid' => $openid,
            'password' => md5($this->password),
            'created_time' => date('Y-m-d H:i:s')
        ]);
        return $id;
    }


    /**
     * 根据用户id更新openid
     * @param $id
     * @param $openid
     */
    public function updateUserOpenid($id,string $openid)
    {
        $row = DB::table('member')->where('id',$id)->update(['openid' => $openid]);
        return $row;
    }


}