<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\MemberService;
use App\Service\JwtAuth;
use Gaoming13\HttpCurl\HttpCurl;

class UserController extends Controller
{
    /**
     * 用户service层
     * @var $memberService
     */
    protected $memberService;

    public function __construct(MemberService $memberService)
    {
        parent::__construct();
        $this->memberService = $memberService;
    }

    /**
     * 测试
     * @return mixed
     */
    public function index()
    {
        //测试用swoole异步处理
//        list($body) = HttpCurl::request('https://api.mymealwell.cn/task', 'POST',[
//            'id' => '666'
//        ]);
        return Result(200,'success');
    }


    /**
     * 用户手机号码登录
     * @return mixed
     */
    public function login()
    {
        $params = $this->request->only(['smscode', 'mobile','realname']);
        $rule = [
            'mobile' => 'required|string|mobile',
            'smscode' => 'required|integer',
            'realname' => 'required|string'
        ];
        $this->apiCheckParams($params, $rule);

        if(strlen($params['realname']) < 2 || is_numeric($params['realname']) ){
            return Result(0,'请填写正确的用户名!');
        }

        $params['weixinunionid'] = $this->request->input('weixinunionid','');
        $params['weixinopenid'] = $this->request->input('weixinopenid','');
        $params['referee_id'] = $this->request->input('referee_id',0);
        $params['weixinface'] = $this->request->input('weixinface','');
        $params['weixinname'] = $this->request->input('weixinnickname','');
        $result = $this->memberService->checkUser($params);
        return Result(200,'success',$result);
    }


    /**
     * 微信用户一键登录(微信获取手机号码，暂时不用)
     * @param string $code
     * @param string $iv
     * @param string $encrypted_data
     * @return mixed
     */
    public function weixLoginNew()
    {
        $params = $this->request->only(['code', 'iv','encrypted_data']);
        $rule = [
            'code' => 'required|string',
            'iv' => 'required|string',
            'encrypted_data' => 'required|string',
        ];
        $this->apiCheckParams($params, $rule);
        $result = $this->memberService->weixLoginNew($params);
        return Result(200,'success',$result);
    }


    /**
     * 获取微信用户openid
     * @return mixed
     */
    public function getOpenId()
    {
        $params = $this->request->only(['code']);
        $rule = [
            'code' => 'required|string',
        ];
        $this->apiCheckParams($params, $rule);
        $result = $this->memberService->getUserOpenId($params);
        return Result(200,'success',$result);
    }

    /**
     * 获取vip充值卡
     * return void
     */
    public function vipCardList()
    {
        $result = $this->memberService->getVipCardList();
        return Result(200,'success',$result);
    }
}
