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
    Route::post('/users','UserController@index');

    /** 微信小程序一键登录获取手机号码 */
    Route::post('/user/weixin_login','UserController@weixLogin');

    /** 获取微信用户openid */
    Route::post('/user/get_openid','UserController@getOpenId');

    /** vip卡列表 */
    Route::get('/user/vip_card_list','UserController@vipCardList');

    /** 微信小程序食谱分类列表 */
    Route::post('/menu/list','MenuController@getList');

    /** 用户充值vip */
    Route::post('/charge/vip','ChargeController@rechargeVip');

    /** 微信小程序支付回调 */
    Route::post('/charge/weixin_callback','ChargeController@chargeCallback');

});

