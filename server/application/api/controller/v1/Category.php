<?php

namespace app\api\controller\v1;

use app\api\model\Category as CategoryModel;
use app\lib\exception\CategoryException;


class Category
{
    /**
     * 获得所有的分类信息
     * @return 所有分类信息（分类标题图，分类名）
     */
    public function getAllCategories(){
        //获得分类信息。分类标题图关联图片模型获取详细地址
        $catetories = CategoryModel::all([],'img');
        if ($catetories->isEmpty()) {
            throw new CategoryException();
        }
        return $catetories;
    }
    
}