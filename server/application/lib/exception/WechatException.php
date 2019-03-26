<?php

namespace app\lib\exception;

/**
 * 微信接口系列异常信息
 */

class WechatException extends BaseException 
{
    public $code = 400;
    public $msg = '微信接口调用失败';
    public $errorcode = 999;
}