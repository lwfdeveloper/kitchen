<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'member';

    /**
     * 根据id获取用户信息
     * @param $id
     * @return array
     */
    public function queryByMobileOne($mobile)
    {
        $user = DB::table('member')->select('id','mobile','realname','openid','weixinname','weixinface','referee_id','integral')->where('mobile', $mobile)->first();
        return $user;
    }
    //将密码进行加密
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

}