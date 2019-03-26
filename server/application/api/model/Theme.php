<?php

namespace app\api\model;


/**
 * 专题信息模型
 * 该模型对应的表存储专题信息
 * 
 */
class Theme extends BaseModel
{
    protected $hidden = [
        'delete_time','update_time','topic_img_id','head_img_id'
    ];

    /**
     * 主题图
     * 一对多相对的关联（该表中提供外键）
     * 关联模型：Image 图片信息表 （该表中提供主键）
     * 
     */
    public function topicImg(){
        return $this->belongsTo('Image','topic_img_id','id');
    }

    /**
     * 专题页面头图
     * 一对多相对的关联（该表中提供外键）
     * 关联模型：Image 图片信息表 （该表中提供主键）
     */
    public function headImg(){
        return $this->belongsTo('Image','head_img_id','id');
    }

    /**
     * 专题下的所有商品信息
     * 多对多相对的关联
     * 关联模型：Product 商品信息表 （该表中提供主键）
     * 中间表：theme_product 专题商品信息表   product_id外键 关联商品信息表   theme_id关联键  关联本表
     */
    public function products(){
        return $this->belongsToMany('Product','theme_product','product_id','theme_id');
    }

    /**
     * 根据专题id查询专题下的所有数据
     * @param $id  专题id
     * @return  专题的所有信息
     */
    public static function getThemeWithProducts($id){
        $theme = self::with('products,topicImg,headImg')->find($id);
        return $theme;
    }












}