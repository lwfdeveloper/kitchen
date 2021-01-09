<?php

namespace App\Http\Middleware;

use App\Models\RequestApiLog as RequestApiLogModel;
use Closure;

class RequestApilog
{
    protected $token = '';
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $url = $request->url();
        //带查询字符串...
        //url = $request->fullUrl();
        $params = $request->all();
        $method = $request->method();
        $header = $request->header();
        $token = isset($header['token']) ? $header['token'][0] : $this->token;
        $response = $next($request);
        $result = $response->getData();
        $result = json_encode($result,true);
        //记录日志
        RequestApiLogModel::createLog($url,$params,$result,$method,$token);

        return $response;
    }
}
