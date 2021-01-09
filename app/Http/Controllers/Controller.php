<?php

namespace App\Http\Controllers;

use http\Client\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
//use App\Validate\ApiValidate;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Request实例
     */
    protected $request;

    /**
     * 应用实例
     */
    protected $apiValidate;

    /**
     * 构造方法
     * @access public
     * @param  Request  $request  Request实例
     */
    public function __construct()
    {
        $this->request = Request();
//        $this->apiValidate = $apiValidate;
        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize(){}

    /**
     * api接口参数必要验证码
     * @param $params
     * @param $rule
     */
    public function apiCheckParams($params, $rules)
    {
        try {
            $validator = Validator::make($params, $rules);
            if($validator->fails()) {
                return Result(403, $validator->errors()->first());
            }
            return true;
        }catch (\Exception $exception){
            return Result(0,$exception->getMessage());
        }
    }
}
