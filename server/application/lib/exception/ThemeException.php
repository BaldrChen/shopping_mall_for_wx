<?php

namespace app\lib\exception;

/**
 * 专题系列异常信息
 */

class ThemeException extends BaseException 
{
    public $code = 404;
    public $msg = '指定主题不存在，请检查主题id';
    public $errorcode = 30000;
}