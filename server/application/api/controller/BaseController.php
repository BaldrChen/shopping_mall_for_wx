<?php

namespace app\api\controller;

use think\Collection;
use app\api\service\Token as TokenService;


/**
 * 基类控制器
 */

class BaseController extends Collection
{
    /**
     * 检验访问权限，该接口允许cms管理员及普通用户访问
     */
    protected function checkPrimaryScope(){
        TokenService::needPrimaryScope();
    }
    
    /**
     * 检验访问权限，该接口允许普通用户访问
     */
    protected function checkExclusiveScope(){
        TokenService::needExclusiveScope();
    }
    
}