<?php

namespace app\lib\exception;

use app\lib\exception\BaseException;


/**
 * 权限系列异常信息
 */
class ForbiddenException extends BaseException
{
    public $code = 403;
    public $msg = '权限不足';
    public $errorCode = 10002;
}