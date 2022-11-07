# PHP Extensions needed

* php-mbstring
* php-gd


## install php extensions in docker

### alpine

```
docker exec -it {container id} sh
apk add php-mbstring
...
```

### php-fpm:alpine

```
docker exec -it {container id} sh
apk add \
    zlib-dev \
    libpng-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd
...
```
