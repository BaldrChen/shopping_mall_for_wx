import { Config } from '../utils/config.js';

class Token{
  constructor() {
    // token有效性验证api地址
    this.verifyUrl = Config.restUrl + 'token/verify';
    // 重新获取token的api地址
    this.tokenUrl = Config.restUrl + 'token/user';
  }
  
  /**
   * token有效性验证
   */
  verify(){

    var token = wx.getStorageSync('token');
    // token不存在则重新获取
    if(!token){
      this.getTokenFromServer();
    }else{
      // 去服务器进行验证有效性
      this._veirfyFromServer(token);
    }
  }

  /**
   * 从服务器获取token
   */
  getTokenFromServer(callback){
    var that = this;
    wx.login({
      success:function(res){
        wx.request({
          url: that.tokenUrl,
          method:'POST',
          data:{
            code:res.code
          },
          success:function(res){

            wx.setStorageSync('token',res.data.token );
            callback && callback(res.data.token);
          }
        })
      }
    })
  };

  /**
   * 去服务器校验token
   * @param token  需要校验的token
   */
  _veirfyFromServer(token){
    var that = this;
    wx.request({
      url: that.verifyUrl,
      method:'POST',
      data:{
        token:token
      },
      success:function(res){
        var valid = res.data.isValid;
        if(!valid){
          that.getTokenFromServer();
        }
      }
    })
  }


}


export { Token };