# kenshoo
This is a simply tool to update kenshoo csv every day.

composer self-update
composer install

1.Rename config_templete.php to config.php
2.Modify settings in config.php
3.Copy csv file which you want to upload in upload folder
4.Add a daily cron job as /usr/bin/php -q /var/kenshoo/controller.php >> /var/kenshoo/log.log

#casperjs
- 安裝必要套件
```sh
    sudo apt-get update
    sudo apt-get install build-essential g++ flex bison gperf ruby perl \
            libsqlite3-dev libfontconfig1-dev libicu-dev libfreetype6 libssl-dev \
            libpng-dev libjpeg-dev
```

### 使用 node 安裝 casperjs & phantomjs
```sh
    sudo apt-get update
    sudo apt-get install nodejs nodejs-legacy

    mkdir -p /usr/developer-tool/casperjs
    cd /usr/developer-tool/casperjs

    npm install phantomjs
    sudo ln -s /usr/developer-tool/casperjs/node_modules/phantomjs/bin/phantomjs  /usr/bin/phantomjs
    phantomjs -v

    npm install casperjs
    sudo ln -s /usr/developer-tool/casperjs/node_modules/casperjs/bin/casperjs  /usr/local/bin/casperjs
    casperjs --version
```

### setting
```sh
    cp casperjs/config/config-template.js casperjs/config/config.js
    vi casperjs/config/config.js
```

### run
```sh
    casperjs casperjs/pinterest-login-and-download-csv.js
```

### crontab
```sh
    crontab -e
    /usr/local/bin/casperjs /var/www/kenshoo/casperjs/pinterest-login-and-download-csv.js >> /var/www/kenshoo/tmp/casperjs.log
```

### 啟動 worker
```sh
    cd queue/
    php failCall.gearman-worker.php &
```
