<?php

namespace app\api\model;

/**
 * 订单信息模型
 * 该模型对应的表存储订单信息
 * 
 */


class Order extends BaseModel
{
    protected $hidden = ['user_id','update_time','delete_time'];

    /**
     * 获取器，自动将商品详细信息由json转为数组（订单下的详细信息存入数据库时转为json格式存入）
     * @param $value 订单下的所有商品详细信息
     */
    public function getSnapItemsAttr($value){
        if (empty($value)) {
            return null;
        }
        return json_decode($value);
    }

    /**
     * 获取器，自动将用户下单地址详细信息由json转为数组（用户下单地址存入数据库时转为json格式存入）
     * @param $value 用户下单地址
     */ 
    public function getSnapAddressAttr($value){
        if (empty($value)) {
            return null;
        }
        return json_decode($value);
    }


    /**
     * 由用户id及第几页获取订单信息
     * @param $uid  当前查询的用户id
     * @param $page 当前页数 客户端每次翻到最底部则分页+1重新请求
     * @param $size 每页几条数据  
     * @return  用户的订单信息
     */
    public static function getSummaryByUser($uid,$page=1,$size=15){

        $pagingData = self::where('user_id','=',$uid)->order('create_time desc')->paginate($size,true,['page'=>$page]);
        
        return $pagingData;
    }


    /**
     * 由第几页获取所有订单信息
     * @param $page 当前页数 客户端每次翻到最底部则分页+1重新请求
     * @param $size 每页几条数据  
     * @return  所有的订单信息
     */  
    public static function getSummaryByPage($page=1,$size=20){
        $pagingData = self::order('create_time desc')->paginate($size,true,['page'=>$page]);
        return $pagingData;
    }

}