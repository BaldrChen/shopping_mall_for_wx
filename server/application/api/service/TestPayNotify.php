<?php
namespace app\api\service;

use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;
use app\lib\enum\OrderStatusEnum;
use app\api\model\Product;
use think\Exception;
use think\facade\Log;
use think\Db;


class  TestPayNotify
{
    /**
     * 测试使用的支付流程   无需付款默认是支付成功
     * @param $data 订单号
     * @return 操作是否成功 true flase
     * 
     */
    public function NotifyProcess($data)
    {

            //获取订单号
            $orderNo = $data;
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


        
    }


    //检根据库存量检测结果，给予不同的订单完成状态
    private function updateOrderStatus($orderID,$success){
        $status = $success?OrderStatusEnum::PAID : OrderStatusEnum::PAID_BUT_OUT_OF;
        OrderModel::where('id','=',$orderID)->update(['status' => $status]);
    }

    //订单支付成功 减去商品库存量
    private function reduceStock($stockStatus){
        $stockStatus = $stockStatus['pStatusArray'];
        foreach ($stockStatus as $productInfo) {
            Product::where('id','=',$productInfo['id'])->setDec('stock',$productInfo['counts']);
        }
    }








}