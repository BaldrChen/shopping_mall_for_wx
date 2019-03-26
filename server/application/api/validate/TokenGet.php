<?php
namespace app\api\validate;

/**
 * 获取token前的验证器
 * 验证小程序传入的登录凭证code
 */

class TokenGet extends Basevalidate
{
    protected $rule = [
        'code' => 'require|isNotEmpty'
    ];

    protected $message = [
        'code' => '未传入code'
    ];
}