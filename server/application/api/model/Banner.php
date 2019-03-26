<?php

namespace app\api\model;


use think\Model;
 
/**
 * 展示图信息模型
 * 该模型对应的表存储各轮播图信息
 * 
 */

class Banner extends BaseModel
{
    protected $hidden = ['update_time','delete_time'];
    /**
     * 一对多关联（该表中提供主键）
     * 关联模型：BannerItem 横幅详细信息表 （该表中提供外键）
     */   
    public function items(){
        return $this->hasMany('BannerItem','banner_id','id');
    }

    
    /**
     * 通过bannerID获取横幅信息
     */
    public static function getBannerByID($id){

        $banner = self::with(['items','items.img'])->find($id);

        return $banner;
    }
}