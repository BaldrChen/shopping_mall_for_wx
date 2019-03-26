<?php


namespace app\api\validate;

/**
 * 传入多个id格式验证器
 */

class IDCollection extends Basevalidate
{
    protected $rule = [
        'ids' => 'require|checkIDs',
    ];

    protected $message = [
        'ids' => 'ids参数必须是以逗号分隔的多个正整数'
    ];


    /**
     * 多个id格式验证
     * 
     */
    protected function checkIDs($value)
    {
        //将字符串格式转化为数组
        $values = explode(',', $value);
        //如果不存在则不通过
        if (empty($values)) {
            return false;
        }
        //遍历所有的值，看是否为正整数
        foreach ($values as $val) {
            if (!$this->isPositiveInteger($val)) {
                return false;
            };
        }

        return true;
    }
}
