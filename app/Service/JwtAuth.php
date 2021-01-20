<?php

namespace App\Service;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Illuminate\Support\Facades\Redis;
use DateTimeImmutable;

class JwtAuth
{
    protected static $key = '8CuHaG5jXRL7lu1eStiueb57aWMJpajnrzoe4vBF4Ebnfg396EoIXu6j2mE8dZkV';
    protected static $url = 'https://scf-api.mymealwell.cn';
    protected static $jwtId = '2mE8dZkV';
    public static $authSuccess=200;
    public static $authOverdue=403; //token过期
    public static $authVerifyError=401; //jwtToken签发tokenId验证不通过
    
    /**
     * 配置秘钥加密
     * @return Configuration
     */
    public static function getConfig()
    {
        $configuration = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::base64Encoded(self::$key)
        );
        return $configuration;
    }

    /**
     * 签发令牌
     */
    public static function createToken($user_id)
    {
        $config = self::getConfig();

        $now = new DateTimeImmutable();

        $token = $config->builder()
            // 签发人
            ->issuedBy(self::$url)
            // 受众
            ->permittedFor(self::$url)
            // JWT ID 编号 唯一标识
            ->identifiedBy(self::$jwtId)
            // 签发时间
            ->issuedAt($now)
            // 在1分钟后才可使用
            //->canOnlyBeUsedAfter($now->modify('+1 minute'))
            // 过期时间2小时
            ->expiresAt($now->modify('+2 hour'))
            // 自定义uid 额外参数
            ->withClaim('user_id', $user_id)
            // 自定义header 参数
            ->withHeader('foo', 'bar')
            // 生成token
            ->getToken($config->signer(), $config->signingKey());

        //result:
        //eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImZvbyI6ImJhciJ9.eyJpc3MiOiJodHRwOlwvXC9leGFtcGxlLmNvbSIsImF1ZCI6Imh0dHA6XC9cL2V4YW1wbGUub3JnIiwianRpIjoiNGYxZzIzYTEyYWEiLCJpYXQiOjE2MDk0Mjk3MjMsIm5iZiI6MTYwOTQyOTc4MywiZXhwIjoxNjA5NDMzMzIzLCJ1aWQiOjF9.o4uLWzZjk-GJgrxgirypHhXKkMMUEeL7z7rmvmW9Mnw
        //base64 decode:
        //{"typ":"JWT","alg":"HS256","foo":"bar"}{"iss":"http:\/\/example.com","aud":"http:\/\/example.org","jti":"4f1g23a12aa","iat":1609429723,"nbf":1609429783,"exp":1609433323,"uid":1}[6cb`"*Gr0ńxoL

        return $token->toString();
    }

    /**
    * 解析令牌
    */
    public static function parseToken(string $token)
    {
        $config = self::getConfig();
        $token = $config->parser()->parse($token);
        return $token;
    }

    /**
     * token验证
     */
    public static function validateToken(string $token)
    {
        try {
            $stampTime = time();
            $config = self::getConfig();
            $token = $config->parser()->parse($token);
            $jwtId = $token->getClaim('jti');
//        var_dump($token->claims());die;
            if (!isset($jwtId) || $jwtId != self::$jwtId){
                return self::$authVerifyError;
            }
            $expTime = $token->getClaim('exp');
            if ($expTime <= $stampTime){
                return self::$authOverdue;
            }
//        $user_id = $token->getClaim('user_id');
            return self::$authSuccess;
        }catch (\Exception $e){
            return self::$authVerifyError;
        }
    }

    /**
     * 验证令牌
     * 弃用
     */
    public static function validationToken(string $token)
    {
        $config = self::getConfig();
//        assert($config instanceof Configuration);

        $token = $config->parser()->parse($token);
        var_dump($token->claim());die;
        $jwtId = $token->getClaim('jti');
        if (!isset($jwtId)){
            return Result(401,'tokenId 不存在!');
        }
//        assert($token instanceof Plain);

        //Lcobucci\JWT\Validation\Constraint\IdentifiedBy: 验证jwt id是否匹配
        //Lcobucci\JWT\Validation\Constraint\IssuedBy: 验证签发人参数是否匹配
        //Lcobucci\JWT\Validation\Constraint\PermittedFor: 验证受众人参数是否匹配
        //Lcobucci\JWT\Validation\Constraint\RelatedTo: 验证自定义cliam参数是否匹配
        //Lcobucci\JWT\Validation\Constraint\SignedWith: 验证令牌是否已使用预期的签名者和密钥签名
        //Lcobucci\JWT\Validation\Constraint\ValidAt: 验证要求iat，nbf和exp（支持余地配置）

        //验证jwt id是否匹配
        $validate_jwt_id = new \Lcobucci\JWT\Validation\Constraint\IdentifiedBy($jwtId);

       $config->setValidationConstraints($validate_jwt_id);

        //验证签发人url是否正确
        $validate_issued = new \Lcobucci\JWT\Validation\Constraint\IssuedBy(self::$url);
        $config->setValidationConstraints($validate_issued);
        //验证客户端url是否匹配
        $validate_aud = new \Lcobucci\JWT\Validation\Constraint\PermittedFor(self::$url);
        $config->setValidationConstraints($validate_aud);
        //验证是否过期
        $timezone = new \DateTimeZone('Asia/Shanghai');
        $now = new \Lcobucci\Clock\SystemClock($timezone);
        $validate_jwt_at = new \Lcobucci\JWT\Validation\Constraint\ValidAt($now);
        $config->setValidationConstraints($validate_jwt_at);

        $constraints = $config->validationConstraints();
        try {
            $config->validator()->assert($token, ...$constraints);
        } catch (RequiredConstraintsViolated $e) {
            // list of constraints violation exceptions:
            var_dump($e->violations());
        }
    }
}