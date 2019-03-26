<?php

namespace app\api\controller\v1;

use app\api\validate\AddressNew;
use app\api\service\Token as TokenSerive;
use app\api\model\User as UserModel;
use app\lib\exception\UserException;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessException;
use app\api\controller\BaseController;
use app\api\model\UserAddress;



class Address extends BaseController
{
    //权限认证
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'address,getUserAddress']
    ];






    /**
     * 用户地址新增或更改
     * @param POST token  用户token
     * @param POST data   用户地址信息
     * @return  JSON   更新成功信息
     */
    public function address()
    {
        $check = new AddressNew();
        $check->goCheck();
        //从token中获得用户id 再去查询用户详细信息
        $uid = TokenSerive::getUid();
        $user = UserModel::get($uid);

        if (!$user) {
            throw new UserException();
        }
        //过滤用户传入的数据
        $dataArray = $check->getDataByRule(input('post.'));

        $userAddress = $user->address;
        if (!$userAddress) {
            //用户无地址，新增 save来自于关联关系
            $user->address()->save($dataArray);
        } else {
            //用户存在地址，更新  save来自于模型
            $user->address->save($dataArray);
        }

        return json(new SuccessException(), 201);
    }


    /**
     * 获得用户地址信息
     * @return 用户地址信息
     */
    public function getUserAddress(){
        //从token中获得用户id 
        $uid = TokenSerive::getUid();
        $userAddress = UserAddress::where('user_id','=',$uid)->find();
        if (!$userAddress) {
            throw new UserException([
                'msg' => '用户地址不存在',
                'errorCode' => 60001
            ]);
        }
        return $userAddress;
    }
}

