<?php

/**
Admin Routes
*/

Route::namespace('Admin')->prefix('api')->group(function () {
    /** 后台管理员登录 */
    Route::post('/user/login','UserController@userLogin');

    /** 后台统计注册人数 */
    Route::post('/user/statistics','UserController@getUserStatistics');

    /** 后台角色列表 */
    Route::post('/user/role_list','RoleController@getRoleList');
});