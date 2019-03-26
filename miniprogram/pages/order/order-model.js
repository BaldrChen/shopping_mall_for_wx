import {
  Base
} from '../../utils/base.js';

class Order extends Base {
  constructor() {
    super();
    this._storageKeyName = 'newOrder';
  }

  /**
   * 下订单
   * @param  param  订单信息
   * 
   */
  doOrder(param, callback) {
    var that = this;
    var allParams = {
      url: 'order',
      type: 'POST',
      data: {
        products: param
      },
      // data包含订单id 订单号 订单创建时间 订单是否有货
      sCallback: function(data) {
        //创建新订单成功标识
        that.execSetStorageSync(true);
        callback && callback(data);
      },
      eCallback: function() {

      }
    };
    this.request(allParams);
  }

  /**
   * 将已生成新订单的标识写入缓存 
   * my页面调用，发现订单更新进行历史订单刷新
   */
  execSetStorageSync(data) {
    wx.setStorageSync(this._storageKeyName, data);
  }

  /*
  * 拉起微信支付
  * params: norderNumber - {int} 订单id
  * return：callback - {obj} 回调方法 ，返回参数 可能值 
    0:商品缺货等原因导致订单不能支付;  1: 支付失败或者支付取消； 2:支付成功；
  * */
  old_execPay(orderNumber, callback) {
    var allParams = {
      url: 'pay/pre_order',
      type: 'post',
      data: {
        id: orderNumber
      },
      sCallback: function(data) {
        var timeStamp = data.timeStamp;
        //如果包含时间戳。则参数正确，可以调起 支付
        if (timeStamp) {
          wx.requestPayment({
            timeStamp: timeStamp.toString(),
            nonceStr: data.nonceStr,
            package: data.package,
            signType: data.signType,
            paySign: data.paySign,
            success: function() {
              callback && callback(2);
            },
            fail: function() {
              callback && callback(1);
            }
          });
        } else {
          callback && callback(0);
        }
      }
    }
    this.request(allParams);
  }

  /*
  * 发到服务器支付-虚假支付
  * params: norderNumber - {int} 订单id
  * return：callback - {obj} 回调方法 ，返回参数 可能值 
    0:商品缺货等原因导致订单不能支付;  1: 支付失败或者支付取消； 2:支付成功；
  * */
  execPay(orderNumber, callback) {
    var that = this;

    wx.showModal({
      title: '确认支付',
      content: '该版本测试中，目前未开放支付。点击支付直接完成支付',
      cancelText: '取消支付',
      cancelColor: '#ef726c',
      confirmText: '确认支付',
      confirmColor: '#85f240',
      success: function(res) {
        if (res.confirm) {
          var allParams = {
            url: 'test_pay/pay',
            type: 'post',
            data: {
              id: orderNumber
            },
          }
          that.request(allParams);
          callback && callback(2);
        } else if (res.cancel) {
          callback && callback(1);
        }

      },
      fail: function() {

      }
    })





  }


  /**
   * 获得订单的详细内容
   * @param  id  订单id
   */
  getOrderInfoById(id, callback) {
    var that = this;
    var allParams = {
      url: 'order/' + id,
      sCallback: function(data) {
        callback && callback(data);
      },
      eCallback: function() {

      }
    };
    this.request(allParams);
  }

  /**
   * 获取所有的订单 pageIndex从1开始
   * @param  pageIndex  页码数
   */
  getOrders(pageIndex, callback) {
    var allParams = {
      url: 'order/by_user',
      data: {
        page: pageIndex
      },
      type: 'GET',
      sCallback: function(data) {
        callback && callback(data);
      }
    };
    this.request(allParams);
  }

  /**
   * 查询是否有新订单
   * 
   */
  hasNewOrder() {
    var flag = wx.getStorageSync(this._storageKeyName);
    return flag == true;
  }

}

export {
  Order
};