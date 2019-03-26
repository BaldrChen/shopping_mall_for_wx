<?php

namespace app\api\service;

use think\Exception;
use app\lib\exception\WechatException;
use app\lib\exception\TokenException;
use app\api\model\User as UserModel;
use app\lib\enum\ScopeEnum;


class UserToken extends Token
{
    //小程序生成的登录验证code
    protected $code;
    //小程序appid
    protected $wxAppID;
    //小程序appsecret
    protected $wxAppSecret;
    //微信登录验证api地址
    protected $wxLoginUrl;

    function __construct($code)
    {

        $this->code = $code;
        //从配置获得相应信息
        $this->wxAppID = config('wx.app_id');
        $this->wxAppSecret = config('wx.app_secret');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            $this->wxAppID,
            $this->wxAppSecret,
            $this->code
        );
    }

    /**
     * 通过微信登录接口获取微信的身份信息
     * @return 身份令牌
     */
    public function get()
    {
        //调用微信code2Session接口
        $result = curl_get($this->wxLoginUrl);
        $wxResult = json_decode($result,true);
        //接口
        if (empty($wxResult['errcode'])) {
            return $this->grantToken($wxResult);
        } else {   
            // 接口报错，抛出异常 
            $this->processLoginError($wxResult);
        }
    }

    /**
     * 对从微信获得的身份信息进行处理
     * @param $wxResult 微信登录接口调用成功返回值
     * @return 身份令牌token
     */
    private function grantToken($wxResult)
    {
        //获得openid，用户唯一身份标识
        $openid = $wxResult['openid'];
        //根据openid查询用户
        $user = UserModel::getByOpenID($openid);
        //如果用户存在，则获取用户的uid
        if ($user) {
            $uid = $user->id;
        }else{
            //如果用户不存在，则新建一个用户并获得uid
            $uid = $this->newUser($openid); 
        }
        //获得需要缓存的数据
        $cachedValue = $this->cachedValue($wxResult,$uid);
        // 将数据设置缓存，得到本地身份令牌token
        $token = $this->saveToCache($cachedValue);
        return $token;
    }

    /**
     * 将数据存入缓存 该缓存中包含微信的用户身份信息（openid，session_key，unionid）本地用户id、本地身份令牌token
     * @param $cachedValue 需要缓存的数据
     * @return 本地身份令牌token
     */
    private function saveToCache($cachedValue){
        //生成随机的身份令牌token
        $key = self::generateToken();
        //将数据转为json格式
        $value = json_encode($cachedValue);
        // 缓存有效期 7200秒
        $expire_in = config('setting.token_expire_in');
        // 设置缓存，token做为缓存名
        $request = cache($key,$value,$expire_in);
        if (!$request) {
            throw new TokenException([
                'msg' => '服务器缓存异常',
                'errorCode' => 10005,
            ]);
        }

        return $key;
    }



    /**
     * 根据传入值拼凑出需要缓存的所有数据
     * @param $wxResult 微信登录接口调用成功返回值 openid等
     * @param $uid 用户的uid
     * 
     */
    private function cachedValue($wxResult,$uid){
        $cachedValue = $wxResult;
        $cachedValue['uid'] = $uid;
        //用户的权限值
        $cachedValue['scope'] = ScopeEnum::User;
        return $cachedValue;
    }


    /**
     * 根据openid新建一个用户
     * @param $openid  微信的openid
     * @return 用户的uid
     */
    private function newUser($openid){

        $user = UserModel::create([
            'openid' => $openid,
        ]);
        return $user->id;
    }

    /**
     * 微信接口返回错误信息，抛出异常
     */
    private function processLoginError($wxResult)
    {
        throw new WechatException([
            'msg' => $wxResult['errmsg'],
            'errorCode' => $wxResult['errcode'],
        ]);
    }


    



















}



