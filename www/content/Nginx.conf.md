# Nginx config example

* Domain: `fsc.org`
* Web directory: `/var/www/fscphp/www/`

Nginx config:
```
    server {
        listen       80;
        server_name  fsc.org;

        root   /var/www/fscphp/www/;
        index  index.php index.html index.htm;

        location / {
            try_files $uri $uri/ /index.php?$args;
        }

        # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
        location ~ ^/index\.php$ {
            fastcgi_pass   127.0.0.1:9000;
            fastcgi_index  index.php;
            fastcgi_param  SCRIPT_FILENAME  /var/www/fscphp/www$fastcgi_script_name;
            include        fastcgi_params;
        }

        # deny all other php
        location ~ \.php {
            deny  all;
        }

        # deny all md
        location ~ \.md {
            deny  all;
        }

        # deny all txt
        location ~ \.txt {
            deny  all;
        }

        # deny all url
        location ~ \.url {
            deny  all;
        }

        # deny all hidden files
        location ~ /\. {
            deny  all;
        }
    }
```
