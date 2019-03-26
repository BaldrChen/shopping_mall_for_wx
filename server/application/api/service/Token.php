<?php

namespace app\api\service;

use think\facade\Request;
use think\facade\Cache;
use app\lib\exception\TokenException;
use think\Exception;
use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;



class Token
{
    /**
     * 生成随机的身份令牌token
     * 
     */
    public static function generateToken(){
        //32个字符组成随机字符串
        $randChars = getRandChar(32);
        //当前访问的时间戳
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        //salt 盐
        $salt = config('secure.token_salt');
        //用三组字符串进行md5加密
        return md5($randChars.$timestamp.$salt);


    }

    /**
     * 从缓存token中获取指定的key的信息
     * @param  $key 需要获取的信息键 （uid,openid,scope等）
     * @return 执行的key信息
     */
    public static function getTokenVar($key){
        $token = Request::instance()->header('token');
        //从缓存中读取token的信息
        $vars = Cache::get($token);
        if (!$vars) {
            throw new TokenException();
        }else{
            //将信息由json转为数组
            if (!is_array($vars)) {
                $vars = json_decode($vars,true);
            }
            if (array_key_exists($key,$vars)) {
                return $vars[$key] ;
            }else{
                throw Exception('尝试获取的token变量并不存在');
            }
        }
    }
    /**
     * 从缓存token中获取Uid
     */
    public static function getUid(){
        $val = 'uid';
        $uid = self::getTokenVar($val);
        return $uid;
    }

    public static function getScope(){
        $uid = self::getTokenVar('scope');
        return $uid;
    }

    /**
     * 该接口类允许普通用户及cms管理员访问
     * @return  true
     */
    public static function needPrimaryScope(){
        $scope = self::getScope();
        if ($scope) {
            if($scope >= ScopeEnum::User){
                return true;
            }else{
                throw new ForbiddenException();
            }
        }else{
            throw new TokenException();
        }
    }

    /**
     * 该接口类允许普通用户访问
     * @return  true
     */
    public static function needExclusiveScope(){
        $scope = self::getScope();
        if ($scope) {
            if($scope == ScopeEnum::User){
                return true;
            }else{
                throw new ForbiddenException();
            }
        }else{
            throw new TokenException();
        }
    }

    public static function isValidOperate($checkUID){
        if (!$checkUID) {
            throw new Exception('必须输入一个被检测的UID');
        }
        $currentOperateUID = self::getUid();
        if ($currentOperateUID == $checkUID) {
            return true;
        }
        return false;
    }

    /**
     * 验证token
     * @param $token 需要校验的token
     * @return 验证结果
     */
    public static function verifyToken($token){
        //从缓存读取token
        $exist = Cache::get($token);
        if ($exist) {
            return true;
        }else{
            return false; 
        }
    }



}