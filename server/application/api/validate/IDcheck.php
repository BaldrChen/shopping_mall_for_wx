<?php

namespace app\api\validate;
use app\api\validate\Basevalidate;

/**
 * 传入id格式验证器
 */

class IDcheck extends Basevalidate
{
    protected $rule = [
        'id' => "require|isPositiveInteger",
        
    ];

    protected $message = [
        'id' => 'id必须正整数'
    ];


}