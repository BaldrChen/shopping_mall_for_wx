<?php

namespace app\api\service;

use think\Exception;
use app\api\service\Order as OrderService;
use app\api\model\Order as OrderModel;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use app\lib\enum\OrderStatusEnum;
use think\Loader;
use think\facade\Env;
use think\facade\Log;
use think\Log as ThinkLog;
use WxPay\WxPayApi;

require(Env::get('root_path') . 'extend/WxPay/WxPay.Api.php');
require(Env::get('root_path') . 'extend/WxPay/WxPay.config.php');

class Pay
{
    private $orderID;
    private $orderNo;
    

    function __construct($orderID)
    {
        if (!$orderID) {
            throw new Exception('订单号不允许为空');
        } else {
            $this->orderID = $orderID;
        }
    }
    /**
     * 创建微信预支付订单
     * 
     */
    public function Pay()
    {
        //订单有效性检测
        $this->checkOrderValid();
        $orderService = new OrderService();
        //获得订单的状态信息
        $status = $orderService->checkOrderStock($this->orderID);
        //检查库存量
        if (!$status['pass']) {
            return $status;
        }
        //将订单总金额传入发起微信支付
        return $this->makeWxPreOrder($status['orderPrice']);
    }

    /**
     * 创建虚拟预支付订单
     * 
     */
    public function testPay()
    {
        //订单有效性检测
        $this->checkOrderValid();
        $orderService = new OrderService();
        //获得订单的状态信息
        $status = $orderService->checkOrderStock($this->orderID);
        //检查库存量
        if (!$status['pass']) {
            return $status;
        }
        //将订单总金额传入虚拟支付。完成支付流程
        return $this->virtualPay($status['orderPrice']);
    }


    /**
     * 发起虚拟支付
     * 
     */
    private function virtualPay($totalPrice){
        $orderNo = $this->orderNo;
        $notify = new TestPayNotify();
        $payResult = $notify->NotifyProcess($orderNo);
        if(!$payResult){
            throw new OrderException([
                'msg' => '支付失败'
            ]);
        }
 
        return [
            'return_code' => 'SUCCESS',
        ];


    }













    //发起微信支付
    private function makeWxPreOrder($totalPrice){
        //从token中获取openid
        $openid = Token::getTokenVar('openid');
        if (!$openid) {
            throw new TokenException();
        }
        $wxOrderData = new \WxPay\WxPayUnifiedOrder();
        //传入订单号
        $wxOrderData->SetOut_trade_no($this->orderNo);
        $wxOrderData->SetTrade_type('JSAPI');
        //传入总金额
        $wxOrderData->SetTotal_fee($totalPrice * 100);
        //支付界面的描述
        $wxOrderData->SetBody('闽台商城');
        $wxOrderData->SetOpenid($openid);
        //回调地址
        $wxOrderData->SetNotify_url(config('secure.pay_back_url'));

        return $this->getPaySignature($wxOrderData);
    }

    //微信预支付订单
    private function getPaySignature($wxOrderData){
        
        $config = new \WxPay\WxPayConfig();
        $wxOrder = \WxPay\WxPayApi::unifiedOrder($config,$wxOrderData);
        if ($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS') {
            Log::record($wxOrder,'error');
            Log::record('获取预支付订单失败','error');
            throw new OrderException([
                'msg' => $wxOrder['return_msg']
            ]);
        }
        $this->recordPreOrder($wxOrder);
        $signature = $this->sign($wxOrder);
        return $signature;
    }

    //小程序端支付签名
    private function sign($wxOrder){
        $config = new \WxPay\WxPayConfig();
        $jsApiPayData = new \WxPay\WxPayJsApiPay();
        $jsApiPayData->SetAppid(config('wx.app_id'));
        $jsApiPayData->SetTimeStamp((string)time());

        $rand = md5(time() . mt_rand(0,1000));
        $jsApiPayData->SetNonceStr($rand);

        $jsApiPayData->SetPackage('prepay_id='.$wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');
        $sign = $jsApiPayData->MakeSign($config);
        $rawValues = $jsApiPayData->GetValues();
        $rawValues['paySign'] = $sign;

        return $rawValues;
    }


    private function recordPreOrder($wxOrder){
        OrderModel::where('id','=',$this->orderID)->update([
            'prepay_id' => $wxOrder['prepay_id']
        ]);
    }

    /**
     * 验证订单的有效性
     * @return 通过验证
     */
    private function checkOrderValid()
    {
        $order = OrderModel::where('id', '=', $this->orderID)->find();
        if (!$order) {
            throw new OrderException();
        }
        if (!Token::isValidOperate($order->user_id)) {
            throw new TokenException([
                'msg' => '订单与用户不匹配',
                'errorCode' => 10003
            ]);
        }
        if ($order->status != OrderStatusEnum::UNPAID) {
            throw new TokenException([
                'msg' => '订单已被支付',
                'errorCode' => 80003,
                'code' => 400
            ]);
        }
        //将订单号传入类属性
        $this->orderNo = $order->order_no;
        return true;
    }
}

