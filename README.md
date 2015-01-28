# kenshoo
This is a simply tool to update kenshoo csv every day.

1.Rename config_templete.php to config.php
2.Modify settings in config.php
3.Copy csv file which you want to upload in upload folder
4.Add a daily cron job as /usr/bin/php -q /var/kenshoo/controller.php >> /var/kenshoo/log.log
