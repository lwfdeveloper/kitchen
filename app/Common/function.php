<?php
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
function getWeiXinPayConfig($type = 'scf_mp')
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
    }
    return $data;
}


/**
 * 获取微信平台Appid & Secret
 * @param string $type
 * @return string[]
 */
function getConfigAppid($type = 'scf_mp')
{
    $data = [];
    switch ($type){
        case 'scf_mp': //膳厨房小程序
            $data = [
                'appid' => 'wxdd1535c31092332e',
                'secret' => 'bb3e6fae72d175ff1c8859e9074f97a8'
            ];
            break;
        case 'sg_mp': //膳贡小程序
            $data = [
                'appid' => 'wxb01e696b3cabe4f8',
                'secret' => '4c20ed47c3e5d2781f85ce89fb304a86'
            ];
            break;
        case 'sg_gzh': //膳贡公众号
            $data = [
                'appid' => 'wx0fee413044f79b1f',
                'secret' => '377571af400a72f3cd24e86430f7e6e3'
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