// pages/cart/cart.js
import {Cart} from "cart-model.js";
var cart = new Cart();
Page({

  /**
   * 页面的初始数据
   */
  data: {

  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

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
    // 从缓存获取购物车信息
    var cartData = cart.getCartDataFromLocal();

    var cal = this._calcTotalAccountAndCounts(cartData);
    this.setData({
      selectedCounts:cal.selectedCounts,
      selectedTypeCounts: cal.selectedTypeCounts,
      account:cal.account,
      cartData:cartData
    })

  },

  onHide: function () {
    cart.execSetStoragesSync(this.data.cartData);
  },

  /*
  * 计算总金额和选择的商品总数
  * @param data {array} 购物车里的所有商品信息
  */
  _calcTotalAccountAndCounts: function (data) {
    // 购物车里的所有商品数量
    var len = data.length,

    //所需计算的总价格，选中商品
    account = 0,
    //购买商品的总个数
    selectedCounts = 0,
    //购买了几种商品
    selectedTypeCounts = 0;

    //浮点数计算易出现偏差，乘以该系数转为整形
    let multiple = 100;

    for(let i=0;i<len;i++){
      // 商品为选中状态才进行价格计算
      if(data[i].selectStatus){
        account += data[i].counts * multiple * Number(data[i].price) * multiple;
        selectedCounts += data[i].counts;
        selectedTypeCounts++;
      }
    }
    return {
      selectedCounts: selectedCounts,
      selectedTypeCounts: selectedTypeCounts,
      account: account / (multiple * multiple)
    }
  },

  /**
   * 购物车中选择商品
   * 勾选
   */
  toggleSelect:function(event){
    // 获得选中的商品传入的信息
    var id = cart.getDataSet(event,'id');
    var status = cart.getDataSet(event,'status');
    var index = cart.getDataSet(event,'index');
    // 将选中的商品状态取反
    this.data.cartData[index].selectStatus = !status;

    this._resetCartData();
  },

  /**
   * 更新购物车的商品数据
   * 
   */
  _resetCartData:function(){
    /**重新计算总金额和商品总数 */
    var newData = this._calcTotalAccountAndCounts(this.data.cartData);
    this.setData({
      account:newData.account,
      selectedCounts:newData.selectedCounts,
      selectedTypeCounts:newData.selectedTypeCounts,
      cartData:this.data.cartData
    });
  },

  /**
   * 调整商品数目
   */
  changeCounts:function(event){
    // 获得选中的商品传入的信息
    var id = cart.getDataSet(event,'id');
    var type = cart.getDataSet(event,'type');
    var index = cart.getDataSet(event,'index');
    var counts = 1;
    // 点击增加
    if(type=='add'){
      cart.addCounts(id);
    }else{
    // 点击减少
      counts = -1;
      cart.cutCounts(id);
    }
    // 将增加或减少的数量重新计算总金额和商品总数
    this.data.cartData[index].counts += counts;
    this._resetCartData();
  },

  /**
   * 删除商品
   * 
   */
  delete: function (event){
    // 获得选中的商品传入的信息
    var id = cart.getDataSet(event, 'id');
    var index = cart.getDataSet(event, 'index');
    // 从页面数据中删除一项商品
    this.data.cartData.splice(index,1); 
    // 更新购物车的商品数据
    this._resetCartData();
    // 把购物车缓存中的数据进行删除
    cart.delete(id);

  },

  toggleSelectAll:function(event){
    var status = cart.getDataSet(event,'status') == 'true';

    var data = this.data.cartData,
    len = data.length;
    for(let i=0;i<len;i++){
      data[i].selectStatus = !status
    };
    this._resetCartData();

  },
  /**
   * 提交订单
   */
  submitOrder:function(event){
    // 跳转到ordel的订单支付页面
    wx.navigateTo({
      url: '../order/order?account=' + this.data.account + '&from=cart'
    });
  }

})