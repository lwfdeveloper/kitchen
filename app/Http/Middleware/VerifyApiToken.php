<?php

namespace App\Http\Middleware;

use App\Service\JwtAuth;
use Closure;

class VerifyApiToken
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
        $header = $request->header();
        $token = isset($header['token']) ? $header['token'][0] : $this->token;
        if(empty($token)){
            return Result(0,'请求无token,认证失败!');
        }
        $auth = JwtAuth::validateToken($token);
        switch ($auth){
            case 200:
                return $next($request);
            case 401:
                return Result($auth,'系统签发tokenId验证不通过!');
            case 403:
                return Result($auth,'token已过期!');
            default :
                return Result(0,'token验证不通过!');
        }
    }
}
