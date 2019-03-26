<?php

namespace app\api\model;

/**
 * 用户地址信息模型
 * 该模型对应的表存储用户地址信息
 * 
 */

class UserAddress extends BaseModel
{
    protected $hidden = ['id','delete_time','user_id'];


}
