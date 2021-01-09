<?php

namespace App\Validate;

use Illuminate\Support\Facades\Validator;

class ApiValidate extends Validator
{
    public $message = [
        'user_id.required' => 'user_id不能为空',
        'username.required' => 'username不能为空！',
        'mobile.required' => 'mobile不能为空！',

        /**订单中心相关*/
        'order_type.required' => 'order_type必传',
        'order_id.required' => 'order_id不能为空',

    ];
}
