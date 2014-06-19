[<?php echo $config['user'];?>]
listen = 127.0.0.1:<?php echo $config['php_port'];?>
listen.allowed_clients = 127.0.0.1
user = <?php echo $config['user'];?>
group = <?php echo $config['group'];?>

slowlog = /home/<?php echo $config['user'];?>/logs/php-fpm.slow.log
error_log = /home/<?php echo $config['user'];?>/logs/php-fpm.error.log
pm = dynamic
pm.max_children = 30
pm.start_servers = 2
pm.min_spare_servers = 5
pm.max_spare_servers = 7
pm.max_requests = 50
listen.backlog = -1
pm.status_path = /php-fpm_status
request_terminate_timeout = 1200s
rlimit_files = 131072
rlimit_core = unlimited
catch_workers_output = yes
env[HOSTNAME] = $HOSTNAME
env[TMP] = /tmp
env[TMPDIR] = /tmp
env[TEMP] = /tmp
php_admin_flag[log_errors] = on