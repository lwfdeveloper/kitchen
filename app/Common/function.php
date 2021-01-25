<?php

use Illuminate\Support\Facades\Redis;

/**
 * 公共返回成功函数 用于构建返回信息
 *
 * @param integer $code
 * @param string $msg
 * @param array $data
 * @return void
 */
function Result($code ,$msg ,$data = [])
{
    if ($code != 200){
        throw new \Exception($msg,$code);
    }
    $reuslt = [
        'code' => $code,
        'msg' => $msg,
        'data' => $data
    ];
    return response()->json($reuslt)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
}

/**
 * 微信支付配置
 * @param string $type
 * @return array
 */
function getWeiXinPayConfig($type = 'sgt_mp')
{
    $data = [];
    switch ($type){
        case 'scf_mp': //膳厨房小程序
            $data = [
                'appid' => env('WeiXinAppid',''),
                'key' => env('WeiXinPayKey',''),
                'mch_id' => env('WeiXinMchId','')
            ];
            break;
        case 'sgt_mp': //膳贡堂小程序
            $data = [
                'appid' => env('SGTAPPID',''),
                'key' => env('WeixinSgtPAyKey',''),
                'mch_id' => env('WeixinSgtMchId','')
            ];
            break;
    }
    return $data;
}


/**
 * 获取微信平台Appid & Secret
 * @param string $type
 * @return string[]
 */
function getConfigAppid($type = 'sgt_mp')
{
    $data = [];
    switch ($type){
        case 'scf_mp': //膳厨房小程序
            $data = [
                'appid' => env('WeiXinAppid',''),
                'secret' => env('WeiXinSECRET','')
            ];
            break;
        case 'sg_mp': //膳贡小程序
            $data = [
                'appid' => env('SGAPPID',''),
                'secret' => env('SGSECRET','')
            ];
            break;
        case 'sg_gzh': //膳贡公众号
            $data = [
                'appid' => env('SGGZHAPPID',''),
                'secret' => env('SGGZHSECRET','')
            ];
            break;
        case 'sgt_mp': //膳贡堂
            $data = [
                'appid' => env('SGTAPPID',''),
                'secret' => env('SGTSECRET','')
            ];
            break;
    }
    return $data;
}

/**
 * 获取阿里云OSS配置
 */
function getAliOSSConfig($type = 'ali')
{
    $data = [];
    switch ($type){
        case 'ali':
            $data = [
                'key' => env('AliSMSACCESSKEY',''),
                'secret' => env('AliSMSSECRET',''),
                'ossUrl' => env('AliSMSCODETYPE','')
            ];
            break;
    }
    return $data;
}

/**
 * 唯一订单号生成方法
 * @return string
 */
function GetOrderNo($uid){
    /* 选择一个随机的方案 */
    mt_srand((double) microtime() * 10000000);

    return $uid . '-' . date('Ymd') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * 生成微信trade_no
 * @return string
 */
function getWeixinTradeNo()
{
    return rand(000000, 999999) . date('YmdHisu') . rand(000000, 999999);
}

/**
 * 手机号中间四位星号处理
 */
function getMobile($mobile)
{
    return str_replace(substr($mobile, 3, 4), '****', $mobile);
}

/**
 * 姓名中间星号处理
 */
function getUserName($user_name)
{
    $strlen     = mb_strlen($user_name, 'utf-8');
    $firstStr     = mb_substr($user_name, 0, 1, 'utf-8');
    $lastStr     = mb_substr($user_name, -1, 1, 'utf-8');
    return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
}


/**
 * [getTreeMenuData 递归后台菜单树形结构]
 * @param  [type]  $item      [description]
 * @param  integer $parent_id [description]
 * @param  string  $sub       [description]
 * @param  integer $level     [description]
 * @return [type]             [description]
 */
function getTreeMenuData($item = [], $key_id = '', $parent_id = 0, $sub = 'children', $level = 0)
{
    $data = [];
    foreach ($item as $key => $val) {
        if ($val->parent_id == $parent_id) {
            $val->level = $level;
            $arr = getTreeMenuData($item, $key_id, $val->menu_id ? $val->menu_id : $val[$key_id], $sub, $level + 1);
            $val->children = count($arr) ? $arr : '';
            $data[] = $val;
        }
    }
    return $data;
}


/**
 * redis锁
 * @param $cacheKey
 * @param $cacheKeyTimeout  上锁时间
 * @param int $keyMaxTimes  最大上锁次数
 */
function redisLock($cacheKey , $cacheKeyTimeout = 3 , $keyMaxTimes = 0)
{
    $intRet = Redis::incr($cacheKey);
    if($intRet === 1){
        Redis::expire($cacheKey,$cacheKeyTimeout);
        return true;
    }
    /**
     * 当设置了最大加锁次数时，如果尝试加锁次数大于最大加锁次数并且无过期时间则强制解锁
    */
    if($keyMaxTimes > 0 && $intRet >= $keyMaxTimes && Redis::ttl($cacheKey) === -1) {
        Redis::del($cacheKey);
    }
    return false;
}
