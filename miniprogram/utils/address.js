import { Base } from '../utils/base.js';
import { Config } from '../utils/config.js';

class Address extends Base {
  constructor() {
    super();
  }

  /**
   * 根据传入的省市县信息组装地址信息
   * @param res  地址信息数组
   */
  setAddressInfo(res){
    // 数据库的字段名与微信地址api调用的键名不同
    var province = res.provinceName || res.province,
    city = res.cityName || res.city,
    country = res.countyName || res.country,
    detail = res.detailInfo || res.detail;

    var totalDetail = city + country + detail;
    // 如果不存在省。则为直辖市，其他拼凑省份进去
    if(!this.isCenterCity(province)){
      totalDetail = province + totalDetail;
    };
    return totalDetail;

  }

  /**
   * 判断是否为直辖市
   */
  isCenterCity(name){
    var centerCitys = ['北京市','天津市','上海市','重庆市'],
    flag = centerCitys.indexOf(name) >= 0;
    return flag;
  }

  /**
   * 更新保存地址到数据库
   * 
   */
  submitAddress(data,callback){
    data = this._setUpAddress(data);
    var param = {
      url:'address',
      type:'post',
      data:data,
      sCallback:function(res){
        callback && callback(true,res);
      },eCallback(res){
        callback && callback(false,res);
      }
    };
    this.request(param);
  }

  /**
   * 将地址信息字段修改为数据库对应字段
   * 
   */
  _setUpAddress(res){
    var formData = {
      name:res.userName,
      province:res.provinceName,
      city:res.cityName,
      country:res.countyName,
      mobile: res.telNumber,
      detail:res.detailInfo
    }
    
    return formData;
  }

  /**
   * 从数据库获得我的收货地址
   * 
   */
  getAddress(callback){
    var that = this;
    var param = {
      url:'address',
      sCallback:function(res){
        if(res){
          res.totalDetail = that.setAddressInfo(res);
          callback && callback(res);
        }
      }
    };
    this.request(param);
  }


}

export { Address };
