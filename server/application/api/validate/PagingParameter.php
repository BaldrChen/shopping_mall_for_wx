<?php
namespace app\api\validate;

/**
 * 分页参数格式验证器
 * 
 */

class PagingParameter extends Basevalidate
{
    protected $rule = [
        'page' => 'isPositiveInteger',
        'size' => 'isPositiveInteger',
    ];

    protected $message = [
        'page' => '分页参数必须是正整数',
        'size' => '分页参数必须是正整数',
    ];


}