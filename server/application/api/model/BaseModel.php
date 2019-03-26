<?php

namespace app\api\model;

use think\Model;

class BaseModel extends Model
{
    /**
     * 根据数据表中是否为本地图片。拼合本地图片完整路径
     * @param $value 当前查询的的字段原数据
     * @param $data  当前的所有数据数组
     * 
     */
    protected function prefixImgUrl($value,$data){
        $finalUrl = $value;
        //如果是本地图片，则拼合图片路径
        if ($data['from'] == 1) {
            $finalUrl = config('setting.img_prefix').$value;
        }
        return $finalUrl;
    }
}
