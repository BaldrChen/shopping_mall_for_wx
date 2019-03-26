// pages/category/category.js
import {Category} from 'category-model.js'
var category = new Category;
Page({
  data: {
    // 分类模板列表
    transClassArr: ['tanslate0', 'tanslate1', 'tanslate2', 'tanslate3', 'tanslate4', 'tanslate5'],
    // 当前分类的数组下标
    currentMenuIndex: 0,
    // 页面加载logo
    loadingHidden: false,
  },

  onLoad: function () {
    this._loadData();
  },

  /*加载所有数据*/
  _loadData: function (callback) {
    var that = this;
    // 获得所有分类信息
    category.getCategoryType((categoryData) => {
      // 数据绑定
      that.setData({
        categoryTypeArr: categoryData,
        loadingHidden: true
      });

      //获得第一个分类下商品的所有信息 
      that.getProductsByCategory(categoryData[0].id, (data) => {
        // 分类图片，分类名字 及所有商品详情
        var dataObj = {
          procucts: data,
          topImgUrl: categoryData[0].img.url,
          title: categoryData[0].name
        };
        // 数据绑定
        that.setData({
          loadingHidden: true,
          categoryInfo0: dataObj
        });
        callback && callback();
      });
    });
  },



  /**
   * 切换分类
   * 
   */
  changeCategory: function (event) {
    // 从传入的数据中获取id及当前位于分类数组的哪个下标
    var index = category.getDataSet(event, 'index'),

      id = category.getDataSet(event, 'id')
    // 通过css的transform特性来进行分类数据切换
    this.setData({
      currentMenuIndex: index
    });

    //如果数据是第一次请求
    if (!this.isLoadedData(index)) {
      var that = this;
      // 通过点击的分类id来获取当前分类的所有信息
      this.getProductsByCategory(id, (data) => {
        that.setData(that.getDataObjForBind(index, data));
      });
    }
  },

  isLoadedData: function (index) {
    if (this.data['categoryInfo' + index]) {
      return true;
    }
    return false;
  },


  /**
   * 将数据赋值给点击分类模板列表
   * @param index  点击的分类的数组下标
   * @param data  点击的分类所有商品信息
   */
  getDataObjForBind: function (index, data) {
    var obj = {},
      arr = [0, 1, 2, 3, 4, 5],
      // 从所有分类信息中获取分类图片及分类名
      baseData = this.data.categoryTypeArr[index];
    for (var item in arr) {
      if (item == arr[index]) {
        obj['categoryInfo' + item] = {
          procucts: data,
          topImgUrl: baseData.img.url,
          title: baseData.name
        };

        return obj;
      }
    }
  },

  /**
   * 获得某种分类下的所有商品
   * @param id  分类id
   * @param callback  回调函数
   */
  getProductsByCategory: function (id, callback) {
    category.getProductsByCategory(id, (data) => {
      callback && callback(data);
    });
  },

  /*跳转到商品详情*/
  onProductsItemTap: function (event) {
    var id = category.getDataSet(event, 'id');
    wx.navigateTo({
      url: '../product/product?id=' + id
    })
  },

  /*下拉刷新页面*/
  onPullDownRefresh: function () {
    this._loadData(() => {
      wx.stopPullDownRefresh()
    });
  },

  //分享效果
  onShareAppMessage: function () {
    return {
      title: '零食商贩 Pretty Vendor',
      path: 'pages/category/category'
    }
  }

})