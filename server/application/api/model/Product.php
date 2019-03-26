<?php

namespace app\api\model;


/**
 * 商品信息模型
 * 该模型对应的表存储商品信息
 * 
 */

class Product extends BaseModel
{
    protected $hidden = [
        'delete_time', 'category_id', 'from', 'create_time', 'update_time', 'pivot'
    ];

    /**
     * 一对多的关联（该表中提供主键）
     * 关联模型：ProductImage 商品图片信息表 （该表中提供外键）
     */
    public function imgs(){
        return $this->hasMany('ProductImage','product_id','id');
    }

    /**
     * 一对多的关联（该表中提供主键）
     * 关联模型：ProductProperty 商品参数信息表 （该表中提供外键）
     */
    public function properties(){
        return $this->hasMany('ProductProperty','product_id','id');
    }

    /**
     * 获取器，自动将图片地址转为可访问的网址路径
     * @param $value 商品主图的路径（不可直接访问）
     * @param $data  当前数据表的所有数据数组
     */
    public function getMainImgUrlAttr($value, $data)
    {
        return $this->prefixImgUrl($value, $data);
    }


    /**
     * 获得最近更新的商品信息
     * @param $count 商品数量
     * @return 最近更新的商品列表
     */
    public static function getMostRecent($count)
    {
        $products = self::limit($count)->order('id desc')->select();
        return $products;
    }

    /**
     * 根据分类id查询所有商品
     * @param $category_id 分类id
     * @return 该分类下的所有商品信息
     */
    public static function getProductsByCategoryID($category_id)
    {
        $products = self::where('category_id', '=', $category_id)->select();
        return $products;
    }

    /**
     * 获取商品详细信息
     * @param $id  商品id
     * @return  商品详细信息
     */
    public static function getProductDetail($id){
        //通过模型关联，获得image表的图片地址
        $products = self::with([
            'imgs' => function($query){
                $query->with(['imgUrl'])
                ->order('order','asc');
            },'properties'])
            ->find($id);

        return $products;
    }
}
