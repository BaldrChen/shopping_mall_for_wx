<?php

namespace app\lib\exception;

use app\lib\exception\BaseException;

/**
 * 参数系列异常信息
 */

class ParameterException extends BaseException
{
    public $code = 400;
    public $msg = '参数错误';
    public $errorCode = 10000;
}
