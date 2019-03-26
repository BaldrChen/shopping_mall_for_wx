<?php

namespace app\lib\exception;

use app\lib\exception\BaseException;

/**
 * 分类系列异常信息
 */

class CategoryException extends BaseException
{
    public $code = 404;
    public $msg = '指定类目不存在，请检查参数';
    public $errorCode = 50000;
}