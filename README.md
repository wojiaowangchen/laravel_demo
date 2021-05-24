laravel5.8框架基本功能部署<br>
1.log日志功能<br>

2.jwt模块实现<br>
  php artisan make:auth<br>
  php artisan migrate  创建用户表<br>

3.队列
  SendReminderEmail::dispatch($param)->allOnConnection('redis');
  php artisan queue:work redis  启动队列，一般用supervise启动
