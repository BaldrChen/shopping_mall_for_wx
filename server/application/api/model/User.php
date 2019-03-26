<?php

namespace app\api\model;

/**
 * 用户信息模型
 * 该模型对应的表存储用户信息
 * 
 */

class User extends BaseModel
{
    public function address(){
        return $this->hasOne('UserAddress','user_id','id');
    }

    /**
     * 根据openid获得用户信息
     * @param $openid  微信的openid
     * @return  本地用户信息
     */
    public static function getByOpenID($openid){
        $user = self::where('openid','=',$openid)->find();
        return $user;
    }


}


