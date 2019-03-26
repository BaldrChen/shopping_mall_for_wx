<?php

namespace app\api\model;
/**
 * 商品图片信息模型
 * 该模型对应的表存储商品图片信息
 * 
 */


class ProductImage extends BaseModel
{
    protected $hidden = ['img_id','update_time','delete_time'];

    /**
     * 一对多的关联（该表中提供主键）
     * 关联模型：Image 图片信息表 （该表中提供外键）
     */
    public function imgUrl(){
        return $this->belongsTo('Image','img_id','id');
    }



}