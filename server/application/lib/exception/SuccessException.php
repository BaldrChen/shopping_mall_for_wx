<?php

namespace app\lib\exception;

/**
 * 成功请求提示
 */

class SuccessException extends BaseException 
{
    public $code = 201;
    public $msg = 'success';
    public $errorCode = 0;
}