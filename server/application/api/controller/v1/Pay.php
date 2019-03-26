<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\validate\IDcheck;
use app\api\service\Pay as PayService;
use app\api\service\WxNotify;
use think\facade\Env;
/**
 * 微信支付，暂时没有商户号调用
 */

class Pay extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'getPreOrder']
    ];

    /**
     * 获得预订单
     * @return 支付签名 小程序端用于拉起微信支付
     */
    public function getPreOrder($id=''){
        $check = new IDcheck();
        $check->goCheck();

        $pay = new PayService($id);
        return $pay->pay();
    }

    //回调
    public function receiveNotify(){
        $config = new \WxPay\WxPayConfig();
        $notify = new WxNotify();
        $notify->Handle($config);
    }
}