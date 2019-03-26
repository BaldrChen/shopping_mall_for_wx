<?php

namespace app\lib\exception;

use think\exception\Handle;
use Exception;

use think\facade\Log;
use think\facade\Request;

/**
 * 自定义的异常处理的Handler类
 * 此类替换了cofing中TP原来的异常处理的Handler类
 * 在app.php中修改
 * 
 */

class ExceptionHandler extends Handle
{
    private $code;
    private $msg;
    private $errorCode;


    /**
     * 仿TP自带的方法
     * 输出异常信息
     * 
     */
    public function render(\Exception $e)
    {
        //如果传入的异常是继承BaseException类的，则为客户端类异常，显示给客户端具体哪里出错
        if ($e instanceof BaseException) {
            //如果是自定义异常
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;
        }else{
        //如果传入的异常不是是继承BaseException类的，则为服务端类异常，不显示具体的错误信息，只给个模糊提示并将错误写入日志
            if (config('app_debug')) {
                return parent::render($e);
            }else {
                $this->code = 500;
                $this->msg = '服务器内部错误';
                $this->errorCode = 999; 
                $this->recordErrorLog($e);
            }

        }

        $request = Request::instance();

        $result = [
            'msg' => $this->msg,
            'error_code' => $this->errorCode,
            //当前出现异常的网址路径
            'request_url' => $request->url(), 
        ];
        //返回给客户端json格式的异常信息
        return json($result,$this->code);
        
    }

    /**
     * 将异常写入日志
     */
    private function recordErrorLog(\Exception $e){
        Log::write($e->getMessage(),'error');
    }















}