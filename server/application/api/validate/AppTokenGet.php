<?php

namespace app\api\validate;
use app\api\validate\Basevalidate;


/**
 * cms用户登录验证器
 */

class AppTokenGet extends Basevalidate
{
    protected $rule = [
        'ac' => 'require|isNotEmpty',
        'se' => 'require|isNotEmpty'
    ];



}