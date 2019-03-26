<?php

namespace app\api\model;

/**
 * 图片信息模型
 * 该模型对应的表存储图片信息
 * 
 */


class Image extends BaseModel
{
    protected $hidden = ['id','from','update_time','delete_time'];

    /**
     * 获取器，自动将图片地址转为可访问的网址路径
     * @param $value 图片的路径（不可直接访问）
     * @param $data  当前数据表的所有数据数组
     */
    public function getUrlAttr($value,$data){
       return $this->prefixImgUrl($value,$data);
    }



}
