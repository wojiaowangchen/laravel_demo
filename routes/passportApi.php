<?php
/**
 * 注册，登录，退出
 *User:wangerxu
 *Data:2021/4/15
 *Time:下午4:58
 */
Route::namespace('Api')->group(function() {
    Route::get('/user/register', 'PassportController@register');

    Route::get('/user/login', 'PassportController@login');

    Route::get('/user/logout', 'PassportController@logout');

    Route::get('/user/refresh', 'PassportController@refresh');
});
