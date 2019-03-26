import { Base } from "../../utils/base.js";


/*
* 购物车数据存放在本地，
* 当用户选中某些商品下单购买时，会从缓存中删除该数据，更新缓存
* 当用用户全部购买时，直接删除整个缓存
*
*/
class Cart extends Base {
  constructor() {
    super();
    this._storageKeyName = 'cart';
  }

    /*
    * 加入到购物车
    * 如果之前没有样的商品，则直接添加一条新的记录， 数量为 counts
    * 如果有，则只将相应数量 + counts
    * @param:  item - {obj} 商品对象,
    * @param:  counts - {int} 商品数目,
    * */
  add(item, counts) {
    // 从缓存取得购物车数据
    var cartData = this.getCartDataFromLocal();
    //获取购物车中之前是否有该商品
    var isHasInfo = this._isHasThatOne(item.id, cartData);
    //购物车中没有该商品，新增一条数据
    if (isHasInfo.index == -1) {
      item.counts = counts;
      //默认为勾选下单状态
      item.selectStatus = true;
      cartData.push(item);
    } else {
      //购物车中有该商品，直接在原有数量上新加数量
      cartData[isHasInfo.index].counts += counts;
    }
    //写入缓存
    wx.setStorageSync(this._storageKeyName, cartData);

  }

  /**
   * 从缓存中读取购物车数据
   * @param flag {bool} 是否过滤掉不下单的商品
   * 
   */
  getCartDataFromLocal(flag) {
    // 从缓存中读取购物车数据
    var res = wx.getStorageSync(this._storageKeyName);

    if (!res) {
      res = []
    }

    //下单时过滤不下单的商品

    if(flag){
      var newRes = [];
      for(let i=0;i<res.length;i++){
        if(res[i].selectStatus){
          newRes.push(res[i]);
        }
      }
      res = newRes;
    }
    //直接返回所有的商品
    return res;
  }


  /**
   * 计算购物车内商品总数量
   * @param flag {bool} 是否区分商品的选中状态
   */
  getCartTotalCounts(flag){
    // 从缓存中读取购物车数据
    var data = this.getCartDataFromLocal();

    var counts = 0;

    for (let i=0;i<data.length;i++){
      
      if(flag){
        // 如果区分商品的选择状态，则状态为选中的商品数量才会相加
        if(data[i].selectStatus){
          counts += data[i].counts;
        }
      // 若果不区分商品选择状态，则所有商品数量直接相加
      }else{
        counts += data[i].counts;
      }
      
    }
    return counts;
  }



  /**
   * 判断某个商品是否已经被添加到购物车中，并返回这个商品的数据及所在数组中的序号
   * @param  id - {int} 商品id
   * @param  arr - {array} 购物车中所有的商品信息
   */
  _isHasThatOne(id, arr) {
    var item,
      //设置默认下标为-1，如果没被添加到购物车，则返回这个
      result = {index: -1};

    for (let i = 0; i < arr.length; i++) {

      item = arr[i];
      // 该商品已被添加到购物车，返回购物车数组下标及其在购物车的商品详情
      if (item.id == id) {
        result = {
          index: i,
          data: item
        };
        break;
      }
    }
    return result;
  }

  /**
   * 修改商品数量
   * @param id - {int} 商品id
   * @param counts -{int} 数目
  */
  _changeCounts(id,counts){
    // 从缓存中读取购物车数据
    var cartData = this.getCartDataFromLocal(),
    // 判断商品是否已被添加到购物车
    hasInfo = this._isHasThatOne(id,cartData);
    // 必须是购物车内的商品才能修改数量
    if(hasInfo.index != -1){
      // 商品数量大于1才能进行数量的修改
      if(hasInfo.data.counts > 1){
        cartData[hasInfo.index].counts += counts;
      }
    }
    //更新本地缓存
    wx.setStorageSync(this._storageKeyName, cartData); 
  }

  /**
   * 购物车增加商品数目
   * @param id - {int} 商品id
   * 直接调用修改商品数量方法。每次数量+1
   */
  addCounts(id){
    this._changeCounts(id,1);
  };

  /**
   * 购物车减少商品数目
   * @param id - {int} 商品id
   * 直接调用修改商品数量方法。每次数量-1
   */
  cutCounts(id){
    this._changeCounts(id,-1);
  }

  /**
   * 删除购物车中的商品
   * @param id - {array} 商品id列表
   */
  delete(ids){
    // 如果不是数组，则强制转为数组
    if(!(ids instanceof Array)){
      ids = [ids];
    }

    // 从缓存中读取购物车数据
    var cartData = this.getCartDataFromLocal();
    
    for(let i = 0;i<ids.length;i++){
      // 判断商品是否已被添加到购物车
      var hasInfo = this._isHasThatOne(ids[i],cartData);
      //如果是购物车里的商品。则执行删除
      if(hasInfo.index != -1){
        cartData.splice(hasInfo.index,1);
      }
    }
    // 更新缓存
    wx.setStorageSync(this._storageKeyName, cartData);
  }
  
  /**
   * 更新保存微信小程序本地缓存
   */
  execSetStoragesSync(data){
    wx.setStorageSync(this._storageKeyName, data)
  }

}
export{Cart};