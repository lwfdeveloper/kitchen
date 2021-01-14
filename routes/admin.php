<?php

/**
Admin Routes
*/

Route::namespace('Admin')->prefix('api')->group(function () {
    /** 后台管理员登录 */
    Route::post('/user/login','UserController@userLogin');
});