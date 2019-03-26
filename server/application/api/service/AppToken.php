<?php

namespace app\api\service;

use app\lib\exception\TokenException;
use app\api\model\ThirdApp;




class AppToken extends Token
{
    /**
     * 第三方登录验证 通过给予身份令牌 cms
     * @return 身份令牌
     */
    public function get($ac,$se){
        //验证账号密码
        $app = ThirdApp::check($ac,$se);

        if(!$app){
            throw new TokenException([
                'msg' => '授权失败',
                'errorCode' => 10004,
            ]);
        }else{
            //设置cms访问权限
            $scope = $app->scope;
            $uid = $app->id;
            $values = [
                'scope' => $scope,
                'uid' => $uid
            ];

            $token = $this->saveToCache($values);
            return $token;
        }
    }

    /**
     * 将数据存入缓存 
     * @param $cachedValue 需要缓存的数据
     * @return 本地身份令牌token
     */
    private function saveToCache($values){
        //获取随机生成的token
        $token = self::generateToken();
        //获取缓存的有效期
        $expire_in = config('setting.token_expire_in');
        //使用token作为缓存名写入缓存
        $result = cache($token,json_encode($values),$expire_in);
        if (!$result) {
            throw new TokenException([
                'msg' => '服务器缓存异常',
                'errorCode' => 10005
            ]);
        }
        return $token;
    }
}