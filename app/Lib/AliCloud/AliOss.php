<?php

namespace App\Lib\AliCloud;

use \OSS\OssClient;

class AliOss
{
    public static $key = '';
    public static $secret = '';
    public static $ossUrl = '';
    /**
     * 保存小程序码图片到阿里云OSS上
     **/
    public static function SaveWeixinQrcodeFile($imageSrc ,$imgType = 'scf_mp',$id = 'xcx')
    {
        $currentDate = date('Y-m-d');
        $rand = time().rand(1111,9999);
        $savePath = "kitken/weixin/mp/{$imgType}/{$id}/{$currentDate}/{$rand}.png";
        $ossClient = new OssClient(self::$key, self::$secret, self::$ossUrl);
        // 上传图片
        $result = $ossClient->uploadFile('shangong', $savePath, $imageSrc);

        return [
            'filePath' => '/' . $savePath,
            'fileUrl' => $result['oss-request-url'],
        ];
    }
}