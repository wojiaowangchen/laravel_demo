<?php
/**
 * 用户端api路由
 * 必须token才能请求
 *User:wangerxu
 *Data:2021/4/15
 *Time:下午4:40
 */

Route::middleware('user.role:1111')->namespace('Api')->group(function() {
    Route::get('/demo', 'PassportController@demo');
});
