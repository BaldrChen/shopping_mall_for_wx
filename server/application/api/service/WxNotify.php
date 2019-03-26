<?php
namespace app\api\service;

use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;
use app\lib\enum\OrderStatusEnum;
use app\api\model\Product;
use think\Exception;
use think\facade\Log;
use think\Db;

require_once(Env::get('root_path') . 'extend/WxPay/WxPay.Api.php');
require(Env::get('root_path') . 'extend/WxPay/WxPay.config.php');

class WxNotify extends \WxPay\WxPayNotify
{
    /**
     * 微信回调方法入口
     */
    public function NotifyProcess($data, $config, $msg)
    {
        //请求返回信息是否为支付成功
        if ($data['result_code'] == 'SUCCESS') {
            //获取订单号
            $orderNo = $data['out_trade_no'];
            Db::startTrans();
            try {
                //根据订单号查出该订单信息
                $order = OrderModel::where('order_no','=',$orderNo)->lock(true)->find();
                //如果订单状态是待支付
                if ($order->status == '1') {
                    $service = new OrderService();
                    //检查订单状态（库存量是否足够）
                    $stockStatus = $service->checkOrderStock($order->id);
                    //库存量足够。订单状态改成已支付并减去库存量
                    if ($stockStatus['pass']) {
                        $this->updateOrderStatus($order->id,true);
                        $this->reduceStock($stockStatus);
                    }
                    //库存量不足，订单状态更改为已支付但库存不足
                    else{
                        $this->updateOrderStatus($order->id,false);
                    }
                }
                Db::commit();
                return true;
            } catch (Exception $ex) {
                Log::error($ex);
                return false;
            }


        }else{
            return true;
        }
    }


    //检根据库存量检测结果，给予不同的订单完成状态
    private function updateOrderStatus($orderID,$success){
        $status = $success?OrderStatusEnum::PAID : OrderStatusEnum::PAID_BUT_OUT_OF;
        OrderModel::where('id','=',$orderID)->update(['status' => $status]);
    }

    //订单支付成功 减去商品库存量
    private function reduceStock($stockStatus){
        foreach ($stockStatus as $productInfo) {
            Product::where('id','=',$productInfo['id'])->setDec('stock',$productInfo['counts']);
        }
    }








}