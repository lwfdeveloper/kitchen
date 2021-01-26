<?php

use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::namespace('Api')->prefix('v1')->middleware('apilog')->group(function () {
    /** 测试 */
//    Route::post('/users','UserController@index')->middleware('api.auth');
    /** 测试异步处理service */
    Route::get('/users','UserController@index');

    /** 发送短信验证码 **/
    Route::post('/sendsms','SmsController@sendSms');

    /** 微信小程序食谱分类列表 */
    Route::post('/menu/list','MenuController@getList');

    /** 生成小程序码*/
    Route::post('/weixin/create_qrcode','WeixinController@createQrcode');


    /** 公共接口部分 */
    Route::group(['prefix'=>'common'],function(){

        /** 获取中国地址三级联动信息 */
        Route::get('/region','CommonController@getRegionInfo');
    });

    /** 用户模块接口 */
    Route::group(['prefix'=>'user'],function(){

        /** 获取微信用户openid */
        Route::post('/get_openid','UserController@getOpenId');

        /** 微信小程序一键登录获取手机号码 */
        Route::post('/weixin_login','UserController@weixLogin');

        /** vip卡列表 */
        Route::get('/vip_card_list','UserController@vipCardList')->middleware('api.auth');
    });


    /** 支付下单相关接口 */
    Route::group(['prefix'=>'charge'],function(){

        /** 微信小程序支付回调 */
        Route::post('/weixin_callback','ChargeController@chargeCallback');

        /** 用户充值vip */
        Route::post('/vip','ChargeController@rechargeVip')->middleware('api.auth');
    });

});

