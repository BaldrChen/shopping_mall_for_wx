<?php

namespace app\api\controller\v1;
use think\Controller;
use app\api\validate\IDcheck;
use app\api\model\Banner as BannerModel;
use app\lib\exception\BannerMissException;

Class Banner extends Controller
{
    /*
     *获取指定id的banner信息
     * 
     */
    public function getBanner($id)
    {
        //id验证
        $validate = new IDcheck;
        $validate ->goCheck();

        $banner = BannerModel::getBannerByID($id);
        if ($banner->isEmpty()) {
            throw new BannerMissException(); 
        } 

        return $banner;

    }
} 