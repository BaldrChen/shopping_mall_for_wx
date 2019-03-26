<?php

namespace app\api\controller\v1;

use app\api\validate\TokenGet;
use app\api\service\UserToken;
use app\lib\exception\ParameterException;
use app\api\service\Token as TokenService;
use app\api\validate\AppTokenGet;
use app\api\service\AppToken;


class Token
{
    /**
     * 获得身份令牌
     * @param $code 小程序获取的登录凭证code
     * @return  服务器加密后的身份令牌
     */
    public function getToken($code='')
    {
        $check = new TokenGet();
        $check->goCheck();

        $ut = new UserToken($code);
        //根据小程序的code获得微信身份信息并得到本地身份令牌token
        $token = $ut->get();
        return [
            'token'=>$token
        ];
    }

    /**
     * 第三方应用获取令牌
     */
    public function getAppToken($ac='',$se=''){

        $check = new AppTokenGet();
        $check->goCheck();
        $app = new AppToken();
        $token = $app->get($ac,$se);
        return[
            'token' => $token
        ];
    }

    /**
     * 校验token是否有效
     * @param  $token 需要验证的token
     * @return  校验结果  true  false
     */
    public function verifyToken($token=""){
        if(!$token){
            throw new ParameterException([
                'token不能为空'
            ]);
        }
        //验证token
        $valid = TokenService::verifyToken($token);
        //返回校验结果
        return[
            'isValid' => $valid
        ];
    }




}