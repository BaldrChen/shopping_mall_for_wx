<?php

namespace app\api\validate;
use app\api\validate\Basevalidate;


/**
 * 用户地址增加或需改验证器
 */

class AddressNew extends Basevalidate
{
    protected $rule = [
        'name' => 'require|isNotEmpty',
        'mobile' => 'require|isNotEmpty',
        'province' => 'require|isNotEmpty',
        'city' => 'require|isNotEmpty',
        'country' => 'require|isNotEmpty',
        'detail' => 'require|isNotEmpty',
    ];



}