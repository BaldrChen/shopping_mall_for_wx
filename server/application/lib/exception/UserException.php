<?php

namespace app\lib\exception;

/**
 * 微信登录接口系列异常信息
 */

class UserException extends BaseException 
{
    public $code = 404;
    public $msg = '微信接口调用失败';
    public $errorcode = 60000;
}