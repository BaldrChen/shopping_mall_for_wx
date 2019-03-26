<?php

namespace app\lib\exception;

/**
 * token系列异常信息
 */

class TokenException extends BaseException 
{
    public $code = 401;
    public $msg = 'Token已过期或无效Token';
    public $errorcode = 10001;
}