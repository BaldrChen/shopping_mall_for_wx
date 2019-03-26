<?php

namespace app\lib\exception;

use think\Exception;

/**
 * 异常信息基类  所有异常信息都要继承此类
 * 
 */


class BaseException extends Exception
{
    //HTTP状态码
    public $code = 400;

    //错误具体信息
    public $msg = '参数错误';

    //自定义错误码
    public $errorCode = 10000;

    /**
     * 构造函数
     * 其他类调用异常抛出时未传入具体的错误信息时则使用默认的错误信息
     * 有传入错误信息时则使用其传入的参数
     */
    public function  __construct($params = [])
    {
        if (!is_array($params)) {
            return;
        }

        if (array_key_exists('msg', $params)) {
            $this->msg = $params['msg'];
        }

        if (array_key_exists('code', $params)) {
            $this->code = $params['code'];
        }

        if (array_key_exists('errorCode', $params)) {
            $this->errorCode = $params['errorCode'];
        }
    }
}

