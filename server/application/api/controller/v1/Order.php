<?php

namespace app\api\controller\v1;

use think\Collection;
use app\api\service\Token as TokenService;
use app\lib\enum\ScopeEnum;
use app\lib\exception\TokenException;
use app\lib\exception\ForbiddenException;
use app\api\validate\OrderPlace;
use app\api\service\Order as OrderService;
use app\api\validate\PagingParameter;
use app\api\model\Order as OrderModel;
use app\api\validate\IDcheck;
use app\lib\exception\OrderException;



class Order extends Collection
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'placeOrder'],
        'checkPrimaryScope' => ['only' => 'getDetail,getSummaryByUser'],
    ];

    /**
     * 由用户id及第几页获取订单信息
     * @param $page 当前页数 简洁查询 客户端每次翻到最底部则分页+1重新请求
     * @param $size 每页几条数据  
     * @return  用户的订单信息
     */
    public function getSummaryByUser($page=1,$size=15){
        //分页参数校验
        $check = new PagingParameter();
        $check->goCheck();


        $uid = TokenService::getUid();
        $pagingOrders = OrderModel::getSummaryByUser($uid,$page,$size);
        //如果查询为空则返回空数组
        if ($pagingOrders->isEmpty()) {
            return[
                'data' => [],
                'current_page' =>$pagingOrders->getCurrentPage()
            ];
        }
        //隐藏不必要的字段并将数据集转为数组
        $data = $pagingOrders->hidden(['snap_items','snap_address','prepay_id'])->toArray();
        $data = $data['data'];
        return [
            'data' => $data,
            'current_page' => $pagingOrders->getCurrentPage()
        ];

    }

    /**
     * 获取订单详情
     * @param $id 订单id
     * @return 订单详细信息 （订单id 订单号 订单状态 订单首个商品图片 订单首个商品名  商品总数量  所有商品详情JSON格式 下单地址JSON格式 ）
     */
    public function getDetail($id){
        $check = new IDcheck();
        $check->goCheck();
    
        $orderDetail = OrderModel::get($id);
        if (!$orderDetail) {
            throw new OrderException();
        }
        return $orderDetail->hidden(['prepay_id']);
    }


    /**
     * 下单
     * @param POST 购买的商品详情等
     * @return 订单详情（ 订单编号，订单id，创建时间，订单是否有货）
     */
    public function placeOrder(){
        $check = new OrderPlace();
        $check->goCheck();
        $products = input('post.products/a');
        $uid = TokenService::getUid();

        //根据用户id及传入的商品详情进行下单
        $order = new OrderService();
        $status = $order->place($uid,$products);
        return $status;
    }


    /**
     * 获取全部订单的简要信息
     * @param $page 当前页数 简洁查询 客户端每次翻到最底部则分页+1重新请求
     * @param $size 每页几条数据  
     * @return  所有的订单信息 
     */
    public function getSummary($page=1,$size = 20){
        $check = new PagingParameter();
        $check->goCheck();
        $pagingOrders = OrderModel::getSummaryByPage($page,$size);
        if ($pagingOrders->isEmpty()) {
            return[
                'current_page' => $pagingOrders->currentPage(),
                'data' => []
            ];
        }
        $data = $pagingOrders->hidden(['snap_items','snap_address'])->toArray();
        $data = $data['data'];
        return [
            'data' => $data,
            'current_page' => $pagingOrders->getCurrentPage()
        ];
    }




}