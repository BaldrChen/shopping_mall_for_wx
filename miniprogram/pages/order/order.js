// pages/order/order.js
import { Cart } from "../cart/cart-model.js";
import { Order } from "../order/order-model.js";
import { Address } from "../../utils/address.js";
var cart = new Cart();
var order = new Order();
var address = new Address();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    id:null 
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    // 判断是购物车进入的订单页面还是我的历史订单进入订单页面
    var from = options.from;
    if(from == 'cart'){
      this._fromCart(options.account);
    }
    if(from == 'order'){
      this._formOrder(options.id);
    }
  },

  /**
   * 从购物车跳转到订单详情页
   * @param account 订单的总价格
   */
  _fromCart: function (account){
    var productsArr;
    this.data.account = account;
    // 从缓存读取购物车数据，并过滤掉未选中的商品
    productsArr = cart.getCartDataFromLocal(true);
    this.setData({
      productsArr: productsArr,
      account: account,
      orderStatus: 0
    });

    //显示收货地址  
    address.getAddress((res) => {
      this._bindAddressInfo(res);
    });
  },


  /**
   * 从历史订单记录跳转到订单详情页
   * @param id 订单id
   */
 _formOrder:function(id){
   if (id) {
     var that = this;
     //下单后，支付成功或者失败。点击右上角返回时同步更新订单状态

     order.getOrderInfoById(id, (data) => {

       that.setData({
         orderStatus: data.status,
         productsArr: data.snap_items,
         account: data.total_price,
         basicInfo: {
           orderTime: data.create_time,
           orderNo: data.order_no
         }
       });

       //快照地址
       var addressInfo = data.snap_address
        // 更新用户地址信息
       addressInfo.totalDetail = address.setAddressInfo(addressInfo);
       that._bindAddressInfo(addressInfo);


     });
   }
 },

  /**
   * 添加或修改用户地址
   * 
   */
  editAddress:function(event){
    var that = this;
    wx.chooseAddress({
      success:function(res){
        var addressInfo = {
          name:res.userName,
          mobile: res.telNumber,
          totalDetail: address.setAddressInfo(res)
        }

        that._bindAddressInfo(addressInfo);

       //保存地址到服务器
        address.submitAddress(res,(flag) =>{
         if(!flag){
           that.showTips('操作提示','地址信息更新失败');
         }
       });

      }
    })
  },

  /**
   * 绑定地址信息
   * 
   */
  _bindAddressInfo:function(addressInfo){
    this.setData({
      addressInfo: addressInfo
    });
  },

  /**
   * 下单和支付
   */
  pay:function(){
    if(!this.data.addressInfo){
      this.showTips('下单提示','请先填写收货地址');
      return;
    }
    if(this.data.orderStatus == 0){
      //从购物车发起支付请求，先到服务器创建订单
      this._firstTimePay();
    }else{
      //从历史订单发起支付请求，不用再创建订单
      this._oneMoresTimePay();
    }
  },



  /**
   * 从购物车发起支付请求
   * 
   */
  _firstTimePay:function(){
    var orderInfo = [],
    procuctInfo = this.data.productsArr,
    order = new Order();
    // 将商品信息及数量加入到订单信息列表
    for(let i = 0;i<procuctInfo.length;i++){
      orderInfo.push({
        product_id:procuctInfo[i].id,
        count:procuctInfo[i].counts
      });
    }
    var that = this;
    //生成订单，根据订单号支付
    order.doOrder(orderInfo,(data)=>{
     //订单生成成功
      if(data.pass){
        //更新订单状态
        var id = data.order_id;
        that.data.id = id;
       // that.data.fromCartFlag = false;

        //开始支付
        that._execPay(id);
      }else{
        that._orderFail(data);//下单失败
      }
    });
  },

  /* 再次次支付*/
  _oneMoresTimePay: function () {
    this._execPay(this.data.id);
  },


  /**
   * 开始支付
   */
  _execPay:function(id){
    var that = this;

    order.execPay(id,(statusCode)=>{
      // 如果返回状态不为失败
      if(statusCode != 0){
        //将已下单的商品从购物车删除
        that.deleteProducts();
        var flag = statusCode == 2;
        //跳转到支付结果页
        wx.navigateTo({
          url: '../pay-result/pay-result?id='+ id + '&flag=' + flag + '&from=order',
        });
      }
    })
  },

  /**
   *将已下单的商品从购物车删除

   */
  deleteProducts:function(){
    var ids = [],arr = this.data.productsArr;
    for(let i=0;i<arr.length;i++){
      ids.push(arr[i].id);
    }
    cart.delete(ids);
  },

  /**
   * 下单失败
   * params: data - {obj} 订单结果信息
   * 
   */
  _orderFail:function(data){
    var nameArr = [],
    name = '',
    str = '',

    pArr = data.pStatusArray;
    for(let i=0;i<pArr.length;i++){
      // api返回信息为缺货
      if(!pArr[i].haveStock){
        name = pArr[i].name;
        // 订单名字大于15个字。则截取前12个加上...
        if(name.length> 15){
          name = name.substr(0,12) +"...";
        }
        // 将所有缺货的商品名字加入同一数组
        nameArr.push(name);

        if(nameArr.length >=2){
          break;
        }
      
      }
    }
    str += nameArr.join('、');
    if (nameArr.length >2){
      str += '等';
    }
    str += '缺货';
    wx.showModal({
      title: '下单失败',
      content: str,
      showCancel:false,
      success:function(res){

      }
    });
  },






  /**
   * 提示窗口
   */
  showTips:function(title,content,flag){
    wx.showModal({
      title: title,
      content: content,
      showCancel: false,
      success:function(res){
        if(flag){
          wx.switchTab({
            url: '/pages/my/my',
          });
        }
      }
    });
  },






  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    if(this.data.id){
      this._formOrder(this.data.id);
    }
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})