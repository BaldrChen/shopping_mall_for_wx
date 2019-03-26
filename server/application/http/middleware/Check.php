<?php

namespace app\http\middleware;

use think\Request;


class Check
{
    public function handle($request, \Closure $next)
    {
        
        $r = new Request();
        header('Access-Control-Allow-Origin:*');  //支持全域名访问，不安全，部署后需要固定限制为客户端网址
        header('Access-Control-Allow-Methods:POST,GET'); //支持的http 动作
        header("Access-Control-Allow-Headers: token,Origin, X-Requested-With, Content-Type, Accept");  //响应头 请按照自己需求添加。

        return $next($request);
    }
}