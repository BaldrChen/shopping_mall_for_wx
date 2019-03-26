<?php

namespace app\api\model;

/**
 * 分类信息模型
 * 该模型对应的表存储分类信息
 * 
 */

class Category extends BaseModel
{
    protected $hidden = ['update_time','delete_time'];

    /**
     * 一对多相对的关联（该表中提供外键）
     * 关联模型：Image 图片信息表 （该表中提供主键）
     */
    public function img(){
        return $this->belongsTo('Image','topic_img_id','id');
    }



}
