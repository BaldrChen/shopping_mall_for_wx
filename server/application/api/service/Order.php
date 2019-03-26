<?php

namespace app\api\service ;

use app\api\model\Product;
use app\lib\exception\OrderException;
use app\api\model\Order as OrderModel;
use app\api\model\OrderProduct;
use think\Exception;
use app\lib\exception\UserException;
use app\api\model\UserAddress;
use think\Db;



class Order
{   
    //客户端传入的商品信息
    protected $oProducts;

    //真实商品信息（数据库）
    protected $products;
    //用户id
    protected $uid;


    /**
     * 根据客户端传入的商品列表
     * @param $uid 用户的id
     * @param $oProducts  客户端传入的用户下单的商品列表
     * @return  订单编号，订单id，创建时间，订单是否有货
     */
    public function place($uid,$oProducts){
        $this->oProducts = $oProducts;
        $this->uid = $uid;
        $this->products = $this->getProductsByOrder($oProducts);
        //获得预订单的状态信息
        $status = $this->getOrderStatus();
        //如果库存量不足 则返回id为-1
        if(!$status['pass']){
            $status['order_id'] = -1;
            return  $status;
        }
        //创建订单
        $orderSnap = $this->snapOrder($status);
        $order = $this->createOrder($orderSnap);
        //设置为有货
        $order['pass'] = true;
        return $order;
    }

    /**
     * 将订单快照写入数据库
     * @param  $sanp  订单快照
     * @return 数组  订单编号，订单id，创建时间
     */
    private function createOrder($snap){
        Db::startTrans();
        try {
            //获得随机订单号
            $orderNo = $this->makeOrderNo();

            $order = new OrderModel();
            $order->user_id = $this->uid;
            $order->order_no = $orderNo;
            $order->total_price = $snap['orderPrice'];
            $order->total_count = $snap['totalCount'];
            $order->snap_img = $snap['snapImg'];
            $order->snap_name = $snap['snapName'];
            $order->snap_address = $snap['snapAddress'];
            //将订单商品详细信息转为json存入数据库
            $order->snap_items = json_encode($snap['pStatus']);
            
            $order->save();
    
            $orderID = $order->id;
            $create_time = $order->create_time;
            //将商品列表中添加订单id
            foreach ($this->oProducts as &$p) {
                $p['order_id'] = $orderID;
            }
            //更新订单商品信息关联表
            $OrderProduct = new OrderProduct();
            $OrderProduct->saveAll($this->oProducts);
            Db::commit();
            return [
                'order_no' => $orderNo,
                'order_id' => $orderID,
                'create_time' => $create_time
            ];
        } catch (Exception $ex) {
            Db::rollback();
            throw $ex;
        }

    }

    /**
     * 根据日期生成随机的订单号
     */
    public static function makeOrderNo(){
        $yCode = array('A','B','C','D','E','F','G','H');
        $orderSn = $yCode[intval(date('Y')) - 2019] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) .substr(microtime(),2,5) . sprintf('%02d',rand(0,99));
        return $orderSn;
    }



    /**
     * 根据预订单的状态信息生成订单快照
     * @param $status  预订单的状态信息
     * @return 订单快照
     */
    private function snapOrder($status){
        $snap = [
            //订单总金额
            'orderPrice' => 0,
            //商品数量
            'totalCount'  => 0,
            //订单下的所有商品详细信息
            'pStatus' =>[],
            //客户下单地址
            'snapAddress' => '',
            //订单上显示的第一个商品名字
            'snapName' =>'',
            //订单上显示的第一个商品图片
            'snapImg' =>''
        ];

        $snap['orderPrice'] = $status['orderPrice'];
        $snap['totalCount'] = $status['totalCount'];
        $snap['pStatus'] = $status['pStatusArray'];
        $snap['snapAddress'] = json_encode($this->getUserAddress());
        $snap['snapName'] = $this->products[0]['name'];
        $snap['snapImg'] = $this->products[0]['main_img_url'];
        //如果商品种类超过一种，则在订单上显示的第一个商品名字后面加个‘等’
        if (count($this->products) > 1) {
            $snap['snapName'] .= '等';
        }
        return $snap;
    }

    /**
     * 获取用户地址
     * @return 用户地址
     */
    private function getUserAddress(){
        $userAddress = UserAddress::where('user_id','=',$this->uid)->find();
    
        if (!$userAddress) {
            throw new UserException([
                'msg' => '用户收货地址不存在，下单失败',
                'errorCode' => '60001',
            ]);
        }
        return $userAddress->toArray();
    }

    /**
     * 验证订单的状态
     * @param  $orderID 订单ID
     * @return 订单的状态信息
     */
    public function checkOrderStock($orderID){
        //获取预处理订单的关联信息（订单与商品详情的关联）
        $oProducts = OrderProduct::where('order_id','=',$orderID)->select();
        $this->oProducts = $oProducts;
        //获取订单中所有的商品数据库中的详细信息
        $this->products = $this->getProductsByOrder($oProducts);
        //获得订单的的状态信息
        $status = $this->getOrderStatus();
        return $status;
    }


    /**
     * 获得订单的的状态信息
     * @return 订单的状态信息
     */
    private function getOrderStatus(){
        $status = [
            //是否有货
            'pass' => true,
            //所有商品的价格总和
            'orderPrice' => 0,
            //商品数量
            'totalCount'=> 0,
            //订单下的所有商品详细信息
            'pStatusArray' => []
        ]; 

        //从订单的详细商品信息中遍历得出当前订单的状态信息
        foreach ($this->oProducts as $oProduct) {
            $pStatus = $this->getProductStatus($oProduct['product_id'],$oProduct['count'],$this->products);
            //检测库存量
            if (!$pStatus['haveStock']) {
                $status['pass'] = false;
            }
            $status['orderPrice'] += $pStatus['totalPrice'];
            $status['totalCount'] += $pStatus['counts'];
            array_push($status['pStatusArray'],$pStatus);
        }
        return $status;
    }


    /**
     * 获得商品的状态信息
     * @param  $oPID 商品id
     * @param  $oCOUNT 商品购买数量
     * @param  $products 订单下的所有商品数据库中的信息
     * @return 商品的状态信息
     */
    private function getProductStatus($oPID,$oCount,$products){
        //商品所在订单详情数组中的下标。默认-1为该订单中不存在该商品
        $pIndex = -1;

        $pStatus = [
            'id' =>null,
            //是否有库存
            'haveStock' =>false,
            //购买数量
            'counts' =>0,
            //商品单价
            'price' =>0,
            //商品名称
            'name' => '',
            //商品总价格
            'totalPrice' => 0,
            //商品图片
            'main_img_url' => null,
        ];

        //根据商品id查找该商品在订单商品详情数组中的下标
        for ($i=0; $i < count($products) ; $i++) { 
            if ($oPID == $products[$i]['id']) {
                $pIndex = $i;
            }
        }

        //如果下标不存在，则报错
        if ($pIndex == -1) {
            throw new OrderException([
                'msg' => 'id为'.$oPID.'商品不存在，创建订单失败'
            ]);
        }else{
            $product = $products[$pIndex];
            //商品id
            $pStatus['id'] = $product['id']; 
            $pStatus['name'] = $product['name']; 
            $pStatus['counts'] = $oCount; 
            $pStatus['price'] = $product['price']; 
            $pStatus['main_img_url'] = $product['main_img_url']; 
            $pStatus['totalPrice'] = $product['price'] * $oCount;
             
            //商品总数量减去订单商品数量。检测库存量是否足够
            if ($product['stock'] - $oCount >= 0 ) {
                $pStatus['haveStock'] = true;
            }
        }


        return $pStatus;
    }

    /**
     * 根据客户端订单信息查找商品真实信息
     * @param  $oProducts  订单与商品关联的信息数组
     * @return $products   订单中所有的商品详细信息
     */
    private function getProductsByOrder($oProducts){
        $oPIDs = [];
        //将一个订单中所有的商品ID遍历出来
        foreach ($oProducts as $item) {
            array_push($oPIDs,$item['product_id']);
        }
        $products = Product::all($oPIDs)->visible(['id','price','stock','name','main_img_url'])->toArray();
        return $products;
    }
}