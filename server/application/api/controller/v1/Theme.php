<?php

namespace app\api\controller\v1;

use think\Controller;
use think\Request;
use app\api\validate\IDCollection;
use app\api\model\Theme as ThemeModel;
use app\lib\exception\ThemeException;
use app\api\validate\IDcheck;

class Theme extends Controller
{
    /**
     * 查询指定的专题信息
     * @param $ids 专题id，可以传入多个 ids=:id1,id2,id3...
     * @return array 专题信息
     */
    public function getSimpleList($ids=''){
        $check = new IDCollection();
        $check->goCheck(); 
        //将传入的ids分割组成数组
        $ids = explode(',',$ids);
        //关联image模型一起查询出专题界面图片及标题图标的路径地址
        $result = ThemeModel::with('topicImg','headImg')->select($ids)->toArray();
        if (!$result) {
            throw new ThemeException();
        }else{
            return $result;
        }
        
    }

    /**
     * 查询一个专题下面的所有数据
     * @param  $id 专题id
     * @return 一个专题下的所有数据（专题信息，专题下的商品信息）
     */
    public function getComplexOne($id){
        $check = new IDcheck();
        $check->goCheck();

        $result = ThemeModel::getThemeWithProducts($id);
        if (!$result) {
            throw new ThemeException();
        }
        return $result;
    }








}
