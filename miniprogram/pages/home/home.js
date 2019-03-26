// pages/home/home.js.js
import{Home} from 'home-model.js';
var home =new Home();
Page({

  /**
   * 页面的初始数据
   */
  data: {

  },

  onLoad:function(){

    this._loadData();

  },


  _loadData:function(){
    var id = 1;
    // 获得首页轮播图信息
    home.getBannerData(id,(data)=>{
      this.setData({
        bannerArr:data
      });
    });
    // 获得首页专题列表信息
    home.getThemeData((data)=>{
      this.setData({
        themeArr:data
      });
    });
    // 获得首页的最新上架上商品信息
    home.getProductsData((data) => {
      this.setData({
        productsArr: data
      });
    });
    





  },

  /**
   * 点击商品后跳转到商品详情
   */
  onProductsItemTap:function(event){
    var id = home.getDataSet(event,'id');

    wx.navigateTo({
      url: '../product/product?id='+id,
    })
  },

  /**
   * 点击专题图后跳转到专题详情
   * 
   */
  onThemeItemTap: function (event) {
    var id = home.getDataSet(event, 'id');
    var name = home.getDataSet(event, 'name');

    wx.navigateTo({
      url: '../theme/theme?id=' + id + '&name=' + name,
    })
  },



})