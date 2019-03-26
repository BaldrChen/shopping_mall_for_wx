<?php

namespace  app\lib\exception;

use app\lib\exception\BaseException;

/**
 * 展示图系列异常信息
 * 
 */

class BannerMissException extends BaseException 
{
    public $code = 404;
    public $msg = '请求的Banner不存在';
    public $errorcode = 40000;
}