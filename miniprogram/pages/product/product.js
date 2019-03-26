 // pages/product/product.js
import { Product } from 'product-model.js';
import { Cart } from '../cart/cart-model.js';
var product = new Product();
var cart = new Cart();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    // 商品id
    id:null,
    // 默认可以下单的商品数量
    countsArray:[1,2,3,4,5,6,7,8,9,10],
    // 加入购物车的商品数量
    productCount:1,
    // 商品详情选项卡切换标识
    currentTabsIndex:0
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

    var id = options.id;
    this.data.id = id
    this._loadData();
  },

  _loadData:function(){
    // 从数据库获取商品详情信息
    product.getDetailInfo(this.data.id,(data)=>{
      this.setData({
        cartTotalCounts:cart.getCartTotalCounts(),
        product:data
      });
      // 如果商品数量小于10，则使用库存的最低数量
      if(data.stock < 10){
        var actualStock = this.getActualStock(data.stock);
        this.setData({
          countsArray: actualStock
        });
      }
    });
  },

  /**
   * 选择购买数量
   */
  bindPickerChange:function(event){

    var index = event.detail.value;
    var selectedCount = this.data.countsArray[index];
    this.setData({
      productCount:selectedCount
    });
  },

  /**
   * 切换商品详情选项卡
   * 
   */
  onTabsItemTap:function(event){
    var index = product.getDataSet(event, 'index');
    this.setData({
      currentTabsIndex:index
    });
  },

  /**
   * 获得商品最低库存的可下单的商品数量
   */
  getActualStock:function($num){
    var arr=[];
    for(var i=1;i<$num+1;i++){
        arr.push(i);
    }
    return arr;
  },

  /**
   * 添加商品到购物车
   */
  onAddingToCartTap:function(event){

    var tempObj = {};
    var keys = ['id','name','main_img_url','price'];
    
    // 将商品数据添加到临时数组中
    for (var key in this.data.product){
      if(keys.indexOf(key) >=0){
        tempObj[key] = this.data.product[key];
      }
    }
    // 加入购物车
    cart.add(tempObj,this.data.productCount);

    var counts = this.data.cartTotalCount + this.data.productCount;
    this.setData({
      cartTotalCounts: cart.getCartTotalCounts()
    });
  },

  onCartTap:function(event){
    wx.switchTab({
      url: '/pages/cart/cart',
    })
  }

})