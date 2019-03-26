<?php 

namespace app\api\validate;

use think\facade\Request;
use think\Exception;
use app\lib\exception\ParameterException;

/**
 * 基类验证器  所有验证器都要继承
 */

class Basevalidate extends \think\Validate
{
    /**
     * 优化自带的数据验证方法，验证失败时抛出自定义格式的异常
     * 
     */
    public function goCheck()
    {
        //获取所有传入的数据
        $params = Request::param();
        //使用自带的验证方法
        $result = $this->batch()->check($params);
        //验证失败抛出自定义格式异常
        if (!$result) {
            $e = new ParameterException([
                'msg' =>  $this->error,
            ]);
            throw $e;
        } else {
            return true;
        }
    }


    /**
     * 验证传入的数值是否为正整数
     */
    protected function isPositiveInteger($value, $rule = '', $data = '', $field = '')
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        }
        return false;
    }


    /**
     * 验证传入的数值是否为空
     */    
    protected function isNotEmpty($value, $rule = '', $data = '', $field = '')
    {
        if (empty($value)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 验证传入的数值是否为正确的手机号码
     */   
    protected function isMobile($value)
    {
        $rule = '^1(3|4|5|7|8)[0-9]\d{8}$^';
        $result = preg_match($rule, $value);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 过滤用户传入的数据
     * @param $array 用户传入的数据
     * @return 过滤后的数组
     */
    public function getDataByRule($array)
    {
        if (array_key_exists('user_id', $array) | array_key_exists('uid', $array)) {
            throw new ParameterException([
                'msg' => '参数中含有非法参数名user_id或uid'
            ]);
        }

        $newArray = [];
        foreach ($this->rule as $key => $value) {
            $newArray[$key] = $array[$key];
        }
        return $newArray;
    }
}
