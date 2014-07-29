# Тестовое задание - Новостная лента

1. Новостная лента состоит из записей с параметрами: название, текст, время создания, время редактирования.

2. нужна возможность просмотреть ленту новостей.

3. Нужна возможность добавлять, редактировать и удалять новости.

4. Необходимо реализовать возможность хранения данных как в MySQL, так и в обычных текстовых файлах. Источник данных указывается в конфигурационном файле и может изменяться в процессе работы с приложением (без миграции данных).

Все, что не описано в задании - на усмотрение программиста.

##### Работающий пример http://nemo.flower.metlan.ru

### INSTALL
```
// создадим базу данных
mysql> create database nemo_test;
Query OK, 1 row affected (0.00 sec)
```

```
// создадим пользователя для доступа к базе данных (пароль рандомный, указан для примера, в коде он же - поменяйте)
mysql> GRANT SELECT, CREATE, INSERT, DELETE, UPDATE  ON nemo_test.* TO 'nemo_test'@'localhost' IDENTIFIED BY 'jhkhj83248972398470234-09123049-2304';
Query OK, 0 rows affected (0.00 sec)
```

```
// таблица для универсальных объектв
CREATE TABLE `objects` (
	`id` INT UNSIGNED NOT NULL PRIMARY KEY auto_increment,
	`create_time` DATETIME DEFAULT '0000-00-00 00:00:00',
	`update_time` DATETIME DEFAULT '0000-00-00 00:00:00',
	`value` text NOT NULL,
	key `key_create_time` (`create_time`),
	key `key_update_time` (`update_time`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
```

```
// аналогичная таблица для хранения новостей
CREATE TABLE `news` (
	`id` INT UNSIGNED NOT NULL PRIMARY KEY auto_increment,
	`create_time` DATETIME DEFAULT '0000-00-00 00:00:00',
	`update_time` DATETIME DEFAULT '0000-00-00 00:00:00',
	`value` text NOT NULL,
	key `key_create_time` (`create_time`),
	key `key_update_time` (`update_time`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
```

### CONFIGURATION
Все настройки находятся в каталоге /configs/
Что бы переключить хранение новостей из базы в файлы и обратно отерактируйте файл /configs/propertis.ini секцию storage_type, параметр news. Он может принимать два значения, file и sql.

### Если что-то не работает
```
php -v
PHP 5.5.10-pl0-gentoo (cli) (built: May 10 2014 21:40:36) 
Copyright (c) 1997-2014 The PHP Group
Zend Engine v2.5.0, Copyright (c) 1998-2014 Zend Technologies
    with Zend OPcache v7.0.3, Copyright (c) 1999-2014, by Zend Technologies
```

```
nginx.conf

    server {
        server_name nemo.example.com;

        root /mnt/files/www/nemo-test;

        # запрещаем скрипты - эта директива должна быть выше редиректов
        location ~* ^/(images|st|js|css)/.+\.(php)$ {
            deny all;
        }

        # определяем каталог для статики
        location ~* ^/(images|st|js|css)/ {
            try_files $uri $uri 404;
            root /mnt/files/www/nemo-test;
        }


        # перенаправляем все запросы на index.php
        location / {
            try_files @index @index;
        }


        # перенаправляем все запросы на index.php
        location @index {
            include /etc/nginx/fastcgi.conf;
            set $fsn /index.php;

            fastcgi_pass unix:/run/php-fpm.socket;
            fastcgi_param  SCRIPT_FILENAME  $document_root$fsn;
        }
```
