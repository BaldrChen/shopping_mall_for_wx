<?php
namespace app\api\validate;

use app\lib\exception\ParameterException;

/**
 * 订单格式验证器
 * 
 */

class OrderPlace extends Basevalidate
{
    protected $rule = [
        'products' => 'checkProducts'
    ];

    protected $singRule = [
        'product_id' => 'require|isPositiveInteger',
        'count' => 'require|isPositiveInteger'
    ];

    /**
     * 验证传入的商品列表信息格式
     * 
     */
    protected function checkProducts($values){

        if (!is_array($values)) {
            throw new ParameterException([
                'msg' => '商品参数不正确'
            ]);
        }

        if (empty($values)) {
            throw new ParameterException([
                'msg' => '商品列表不能为空'
            ]);
        }
        //遍历整个商品列表，对里面的单个商品进行验证
        foreach ($values as $value) {
            $this->checkProduct($value);
        }
        return true;
    }

    /**
     * 验证传入的单个商品信息格式
     * 
     */
    protected function checkProduct($value){
        //因在验证器方法里再次进行验证，所以无法使用TP的自动执行，需手动实例化验证器类进行验证
        $validate = new Basevalidate($this->singRule);
        //使用tp自带的数据验证
        $result = $validate->check($value);

        if (!$result) {
            throw new ParameterException([
                'msg' => '商品列表参数错误',
            ]);
        }
    }
}