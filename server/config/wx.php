<?php
// +----------------------------------------------------------------------
// | 微信接口设置
// +----------------------------------------------------------------------

return [
    'app_id' => 'you appid',
    'app_secret' =>'you app secret',
    'login_url' => "https://api.weixin.qq.com/sns/jscode2session?" .
    "appid=%s&secret=%s&js_code=%s&grant_type=authorization_code"
];