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
    public static $regInter = 10; //邀请注册获得默认10积分
    protected static $state = 1; //积分获取状态1-获取2-消耗

    /**
     * 根据用户id获取信息
     * @param $id
     * @return array
     */
    public function queryByIdOne($id)
    {
        $user = DB::table('member')->select('id as user_id','mobile','weixinopenid','is_doctor')->where('id', $id)->first();
        return $user;
    }

    /**
     * 根据手机号码获取用户信息
     * @param $mobile
     * @return array
     */
    public function queryByMobileOne($mobile)
    {
        $user = DB::table('member')
            ->select('id as user_id','mobile','realname','constion_id','weixinopenid','weixinname','weixinunionid','weixinface','is_doctor','referee_id','integral','help_status')
            ->where('mobile', $mobile)
            ->first();
        return $user;
    }

    /**
     * 新增用户
     * return id int
     */
    public function insertUser(array $data)
    {
        $data['password'] = md5($this->password);
        $data['created_time'] = date('Y-m-d H:i:s');
        $data['balance'] = 0;
        $data['member_time'] = time() + 7 * 86400;//7天会员体验
        $data['register_time'] = time();

        DB::beginTransaction();
        try {
            $id = DB::table('member')->insertGetId($data);
            if (empty($id)){
                DB::rollBack();
            }
            if ($data['referee_id'] > 0){
                DB::table('member_integral_record')->insert([
                    'user_id' => $data['referee_id'],
                    'state' => self::$state,
                    'desc' => '邀请用户注册',
                    'created_time' => $data['created_time'],
                    'integral' => self::$regInter
                ]);
                $this->userIncIntegral($data['referee_id']);
                $this->userIncInvited($data['referee_id']);
            }
            DB::commit();
            return $id;
        }catch (\Exception $e){
            DB::rollBack();
            return false;
        }
    }

    /**
     * 根据用户id自增积分
     * @param int $user_id
     */
    public function userIncIntegral(int $user_id):bool
    {
        DB::table('member')->where('id',$user_id)->increment('integral', 10);
        return true;
    }

    /**
     * 根据用户id自增邀请人数
     * @param int $user_id
     */
    public function userIncInvited(int $user_id):bool
    {
        DB::table('member')->where('id',$user_id)->increment('invitednum', 1);
        return true;
    }

    /**
     * 根据用户id更新openid
     * @param $id
     * @param $openid
     */
    public function updateUserOpenid($id,string $openid)
    {
        $row = DB::table('member')->where('id',$id)->update(['weixinopenid' => $openid]);
        return $row;
    }

    /**
     * 根据openid查询用户信息
     * @param string $openId
     * @return array
     */
    public function queryByOpenidFind(string $openId)
    {
        $data = DB::table('member')->select('id as user_id','mobile','constion_id')->where('weixinopenid',$openId)->first();
        return $data;
    }

    /**
     * 根据unionid查询用户信息
     * @param string $unionid
     * @return array
     */
    public function queryByUnionidFind(string $unionid)
    {
        $data = DB::table('member')->select('id as user_id','mobile','constion_id')->where('weixinunionid',$unionid)->first();
        return $data;
    }


    /**
     * 根据用户id更新用户微信信息
     * @param int $user_id
     * @param string $weixinopenid
     * @param string $nickname
     * @param string $weixinface
     * @param string $weixinunionid
     */
    public function updateUserWeixinInfo(int $user_id,string $weixinopenid,string $nickname,string $weixinface ,$weixinunionid = '')
    {
        $row = DB::table('member')->where('id',$user_id)->update([
            'weixinopenid' => $weixinopenid,
            'weixinface' => $weixinface,
            'weixinname' => $nickname,
            'weixinunionid' => $weixinunionid
        ]);
        return $row;
    }


}
