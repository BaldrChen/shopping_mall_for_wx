<?php

namespace app\api\validate;
use app\api\validate\Basevalidate;

/**
 * 查询商品详情数量验证器
 * 不得少于1个且多于16个
 * 防止大批量查询
 */

class Count extends Basevalidate
{
    protected $rule = [
        'count' => "isPositiveInteger|between:1,16",
        
    ];




}