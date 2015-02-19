# URL Shortener

Simple URL shortener.

## How to deploy?

- Clone this repo
- Configure web server
- Configure database

## Server configuration

Sample config for Nginx:

```nginx
server {
    server_name urlshortener.dev;
    root '/var/www/urlshortener/public';
    index index.php;

    try_files $uri $uri/ /index.php?$args;

    location /index.php {
        include fastcgi_params;
        fastcgi_param DB_HOST 'localhost';
        fastcgi_param DB_NAME 'urlshortener';
        fastcgi_param DB_USER 'user';
        fastcgi_param DB_PASS 'secret';
        fastcgi_pass unix:/var/run/php5-fpm.sock;
    }
}
```

## Database configuration

Schema script for MySQL:

```sql
SET NAMES utf8mb4;

DROP DATABASE IF EXISTS `urlshortener`;
CREATE DATABASE `urlshortener`;
USE `urlshortener`;

DROP TABLE IF EXISTS `urls`;
CREATE TABLE `urls` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `short` varchar(7) DEFAULT NULL,
  `url` varchar(2048) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

## License

The MIT License (MIT)

Copyright (c) 2015 acelot

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.