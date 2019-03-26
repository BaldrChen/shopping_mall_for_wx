import { Config } from '../utils/config.js';
import { Token } from '../utils/token.js';

class Base{
  constructor(){
    // 从配置获取服务器api地址
    this.baseRequestUrl = Config.restUrl;
  }


  /**
   * http 请求类
   * 其他调用http请求都是调用此类
   * @param param 传入的请求参数
   * @param noRefetch 当noRefech为true时 不做未授权重试机制
   */
  request(params,noRefetch){
    var that = this;
    var url = this.baseRequestUrl + params.url;
    // 默认为get
    if(!params.type){
      params.type = 'GET';
    }
    wx.request({
      url: url,
      data: params.data,
      method: params.type,
      header:{
        'content-type':'application/json',
        'token': wx.getStorageSync('token')
      },
     
      success:function(res){
        // 判断以2（2xx)开头的状态码为正确
        // 异常不要返回到回调中，就在request中处理，记录日志并showToast一个统一的错误即可
        var code = res.statusCode.toString();
        var startChar = code.charAt(0);
        
        if(startChar == '2'){
          params.sCallback && params.sCallback(res.data);
        }else{
          // 如果返回的是401.说明身份验证未通过，重新获取token后再重新发起请求
          if(code == '401'){
            //noRefech为false，重试请求
            if(!noRefetch){
              that._refetch(params)
            }
          }
          //noRefech为false，停止继续重试
          if(noRefetch){
          params.eCallback && params.eCallback(res.data);
           }
        }
        
      },
      fail:function(err){
        console.log(err);
      }
    })
  }

  /**
   * 从服务器重新获取token
   */
  _refetch(params){
    var token = new Token();
    token.getTokenFromServer((token)=>{
      this.request(params,true);
    });
  }


  /*获得元素上绑定的值 */
  getDataSet(event,key){
    return event.currentTarget.dataset[key];
  }


}

export { Base };