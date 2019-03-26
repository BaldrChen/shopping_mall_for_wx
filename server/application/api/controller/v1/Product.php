<?php

namespace app\api\controller\v1;

use think\Controller;
use app\api\validate\Count;
use app\api\model\Product as ProductModel;
use app\lib\exception\ProduceException;
use app\api\validate\IDcheck;


class Product extends Controller
{
    /**
     * 获取指定数量的最近商品
     * @param $count 查询的商品数量
     * @return  最近更新的商品列表
     */
    public function getRecent($count = 16)
    {
        
        $check = new Count();
        $check->goCheck();

        $products = ProductModel::getMostRecent($count);

        if ($products->isEmpty()) {
            throw new ProduceException();
        }
        //隐藏不必要的字段
        $products = $products->hidden(['summary']);

        return $products;
    }


    /**
     * 获得某分类下的全部商品
     * @param $id  分类列表id
     * @return 该分类的商品列表
     */
    public function getAllInCategory($id){
        //id校验
        $check = new IDcheck();
        $check->goCheck();

        $products = ProductModel::getProductsByCategoryID($id);

        if ($products->isEmpty()) {
            throw new ProduceException();
        }

        $products = $products->hidden(['summary']);
        return $products;
    }


    /**
     * 获取一条详细的商品信息
     * @param $id  商品id
     * @return  商品详细信息
     */
    public function getOne($id){
        $check = new IDcheck();
        $check->goCheck();
        $product = ProductModel::getProductDetail($id);
        return $product;
    }

}
