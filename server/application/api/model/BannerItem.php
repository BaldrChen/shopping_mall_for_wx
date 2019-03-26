<?php

namespace app\api\model;

use think\Model;
/**
 * 展示图信息子项模型
 * 该模型对应的表存储各轮播图详细信息
 * 
 */

class BannerItem extends BaseModel
{
    protected $hidden = ['id','img_id','banner_id','update_time','delete_time'];
    
    /**
     * 一对多相对的关联（该表中提供外键）
     * 关联模型：Image 图片信息表 （该表中提供主键）
     */
    public function img(){
        return $this->belongsTo('Image','img_id','id');
    }
}
