<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\validate\IDcheck;
use app\api\service\Pay as PayService;
use app\api\service\WxNotify;
use think\facade\Env;


class Testpay extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'getPreOrder']
    ];

    /**
     * 虚拟支付，因没有商户号不能完成微信支付，所以该方法直接返回支付成功
     * @return 
     */
    public function getPreOrder($id=''){
        $check = new IDcheck();
        $check->goCheck();

        $pay = new PayService($id);
        return $pay->testpay();
    }


}