<?php

namespace app\lib\enum;

/**
 * 订单状态属性枚举类
 * 
 */

class OrderStatusEnum
{
    //待支付
    const UNPAID = 1;

    //已支付未发货
    const PAID = 2;

    //已支付已发货
    const DELIVERED = 3;

    //已支付库存不足
    const PAID_BUT_OUT_OF = 4;
    
}