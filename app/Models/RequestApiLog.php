<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RequestApiLog extends Model
{
    /**
     * 记录api请求的日志
     * @param $request_url
     * @param $params
     * @param $result
     * @param string $token
     * return bool
     */
    public static function createLog(string $request_url ,array $params ,string $result ,string $method,$token = '')
    {
        try {
            DB::table('request_log')->insert([
                'request_url' => $request_url,
                'params' => json_encode($params),
                'result' => $result,
                'created_time' => time(),
                'method' => $method,
                'token' => $token
            ]);
            return true;
        }catch (\Exception $exception){
            return false;
        }
    }
}
