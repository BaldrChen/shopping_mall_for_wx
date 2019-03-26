<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------


//主页头图
Route::get('api/:version/banner/:id', 'api/:version.Banner/getBanner');

//专题
Route::get('api/:version/theme', 'api/:version.Theme/getSimpleList');
Route::get('api/:version/theme/:id','api/:version.Theme/getComplexOne');

//商品
Route::get('api/:version/product/recent','api/:version.Product/getRecent');
Route::get('api/:version/product/by_category','api/:version.Product/getAllInCategory');
Route::get('api/:version/product/:id','api/:version.Product/getOne',[],['id'=>'\d+']);

//分类
Route::get('api/:version/category/all','api/:version.Category/getAllCategories');

//用户身份相关
Route::post('api/:version/token/user', 'api/:version.Token/getToken');
Route::post('api/:version/token/verify', 'api/:version.Token/VerifyToken');
Route::post('api/:version/token/app', 'api/:version.Token/getAppToken')->middleware('Check');

//用户地址相关
Route::post('api/:version/address', 'api/:version.Address/address');
Route::get('api/:version/address', 'api/:version.Address/getUserAddress');

//订单相关
Route::post('api/:version/order', 'api/:version.Order/placeOrder');
Route::get('api/:version/order/by_user', 'api/:version.Order/getSummaryByUser');
Route::any('api/:version/order/paginate', 'api/:version.Order/getSummary')->middleware('Check');
Route::get('api/:version/order/:id', 'api/:version.Order/getDetail',[],['id'=>'\d+']);

//支付相关
Route::post('api/:version/pay/pre_order', 'api/:version.Pay/getPreOrder');
Route::post('api/:version/pay/notify', 'api/:version.Pay/receiveNotify');
//伪支付
Route::post('api/:version/test_pay/pay', 'api/:version.Testpay/getPreOrder');











return [

];
