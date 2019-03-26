// pages/my/my.js
import { My } from 'my-model.js';
import { Order } from '../order/order-model.js';
import { Address } from '../../utils/address.js';
var my = new My();
var order = new Order();
var address = new Address();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    // 当前页码数
    pageIndex: 1,
    // 订单列表
    orderArr:[],
    // 订单数据加载完毕标识
    isLoadedAll:false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

    this._loadData();
    this._getAddressInfo();

  },

  /**
   * 获得用户地址信息
   */
  _getAddressInfo:function(){
    address.getAddress((addressInfo)=>{
      this._bindAddressInfo(addressInfo);
    });
  },


  _loadData:function(){
    // 获得用户订单信息
    this._getOrders();

  },

  /**
   * 绑定地址信息
   */
  _bindAddressInfo: function (addressInfo){
    this.setData({
      addressInfo:addressInfo
    });
  },

  /**
   * 获得用户所有的订单信息
   * 翻到最底层再进行加载下一页订单信息
   */
  _getOrders:function(callback){
    // 页数从第一页开始
    order.getOrders(this.data.pageIndex,(res)=>{
      var data = res.data;
      // 返回的订单列表数量不为0，将新获取的订单信息插入到原订单信息后面
      if(data.length>0){
        this.data.orderArr.push.apply(this.data.orderArr,data);
        this.setData({
          orderArr: this.data.orderArr
        });
      }else{
        // 返回的订单为0，代表已经没有数据了。设置页码结束标识
        this.data.isLoadedAll =  true;
      }
      callback && callback();
    });
  },

  /**
   * 显示订单详细信息
   * 
   */
  showOrderDetailInfo:function(event){
    var id = order.getDataSet(event,'id');
    wx.navigateTo({
      url: '../order/order?from=order&id=' + id,
    })
  },

  /**
   * 未支付订单再次支付
   * 
  */
 rePay:function(event){
   var id = order.getDataSet(event,'id');
   var index = order.getDataSet(event,'index');
   this._execPay(id,index);
 },

  /**
   * 订单支付
   * @param id 订单id
   * @param index 订单所在的数组下标
   */
  _execPay:function(id,index){
    var that = this;
    // 使用order类的支付方法支付
    order.execPay(id,(statusCode)=>{
      if(statusCode > 0){
        // 返回的订单支付状态是否为已支付
        var flag = statusCode == 2;

        //返回支付结果为已支付，更新订单状态
        if(flag){
          that.data.orderArr[index].status = 2;
          that.setData({
            orderArr:that.data.orderArr
          });
        }
        //跳转到成功的页面
        wx.navigateTo({
          url: '../pay-result/pay-result?id=' + id + '&flag=' + flag + '&from=my', 
        });
      }else{
        that.showTips('支付失败','商品已下架或库存不足');
      }
    });
  },

  /**
   * 提示窗口
   * 
   */
  showTips:function(title,content){
    wx.showModal({
      title: title,
      content: content,
      showCancel: false,
      success:function(res){

      }
    })
  },


  /**
   * 修改用户地址
   * 
   */
  editAddress: function (event) {
    var that = this;
    // 调用微信地址接口，将数据更新到魂村
    wx.chooseAddress({
      success: function (res) {
        var addressInfo = {
          name: res.userName,
          mobile: res.telNumber,
          totalDetail: address.setAddressInfo(res)
        }

        that._bindAddressInfo(addressInfo);

        //保存地址到服务器
        address.submitAddress(res, (flag) => {
          if (!flag) {
            that.showTips('操作提示', '地址信息更新失败');
          }
        });

      }
    })
  },




  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function (res) {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    // 每次进去我的页面，去缓存查询是否有新订单，有就请求服务器api刷新数据
    var newOrderFlag = order.hasNewOrder();
    if(newOrderFlag){
      this.refresh();
    }
  },

  /**
   * 我的页面订单刷新
   * 当购物车页面添加新订单后，切刀我的页面订单会重新请求一次api更新历史订单数据
   * 没有添加新商品则不再请求api
   */
  refresh:function(){
    var that = this;
    //订单初始化
    this.data.orderArr=[];
    this._getOrders(()=>{
      //订单列表是否加载完全
      that.data.isLoadedAll = false;  
      that.data.pageIndex = 1;
      //更新标志位
      order.execSetStorageSync(false); 
    });
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
   * 用于加载订单列表
   * 每次触底页码+1
   */
  onReachBottom: function () {
    if(!this.data.isLoadedAll){
      this.data.pageIndex++;
      this._getOrders();
    }
  },

  







  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})