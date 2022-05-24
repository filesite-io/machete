# 运行时目录

需要设置此目录权限为777：
```
chmod -R 777 runtime/
```

或者允许php进程所属用户写入：
```
chown -R apache:apache runtime/
```

其中apache为php-fpm进程所属用户。
