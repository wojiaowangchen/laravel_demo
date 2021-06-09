中文手册：https://www.php.cn/php/php-laravel-api-authentication.html
laravel5.8框架基本功能部署<br>
1.log日志功能<br>

2.jwt模块实现<br>
  php artisan make:auth<br>
  php artisan migrate  创建用户表<br>

3.队列<br>
  php artisan make:job ProcessPodcast <br>
  SendReminderEmail::dispatch($param)->allOnConnection('redis');<br>
  php artisan queue:work redis  启动队列，一般用supervise启动<br>

4.httpClient （sdk，curl）<br>


5.laravel-admin(后台系统)<br>
  执行资源发布：php artisan vendor:publish --provider="Encore\Admin\AdminServiceProvider"<br>
  php artisan config:cache<br>
  php artisan admin:install<br>
