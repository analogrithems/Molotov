upstream <?php echo $config['user'].'-';?>php-handler {
    server 127.0.0.1:<?php echo $config['php_port'];?>;
}
server {
        listen 192.168.10.47:80;
	    server_name <?php echo $config['fqdn'];?> <?php if(isset($config['aliases'])) echo $config['aliases'];?>;
	    root /home/<?php echo $config['user'];?>/<?php echo $config['fqdn'];?>/htdocs;
	    access_log /home/<?php echo $config['user'];?>/logs/<?php echo $config['fqdn'];?>-access.log;
	    error_log /home/<?php echo $config['user'];?>/logs/<?php echo $config['fqdn'];?>-error.log;

		<?php
		if(isset($config['sslOnly']) && true === $config['sslOnly']){ 
		?>
	    location / {
	
	            try_files $uri $uri/ index.php;
	    }
	
	    location ~ ^(.+?\.php)(/.*)?$ {
	            try_files $1 = 404;
	
	            include /etc/nginx/fastcgi.conf;
	            fastcgi_param SCRIPT_FILENAME $document_root$1;
	            fastcgi_param PATH_INFO $2;
	            fastcgi_param HTTPS on;
	            fastcgi_pass <?php echo $config['user'].'-';?>php-handler;
	    }
		<?php }else{ ?>
        return 301 https://$host$request_uri;
        <?php
        }
        ?>
}

server {
    listen 192.168.10.47:443 ssl;
    server_name <?php echo $config['fqdn'];?> <?php if(isset($config['aliases'])) echo $config['aliases'];?>;
    root /home/<?php echo $config['user'];?>/<?php echo $config['fqdn'];?>/htdocs;
    access_log /home/<?php echo $config['user'];?>/logs/<?php echo $config['fqdn'];?>-access.log;
    error_log /home/<?php echo $config['user'];?>/logs/<?php echo $config['fqdn'];?>-error.log;

    ssl                  on;
    ssl_certificate      /etc/nginx/sslcerts/<?php echo $config['fqdn'];?>.crt;
    ssl_certificate_key   /etc/nginx/sslcerts/<?php echo $config['fqdn'];?>.key;
    ssl_session_timeout  3m;
    ssl_protocols  TLSv1 TLSv1.1 TLSv1.2;
    ssl_ciphers !aNULL:!eNULL:FIPS@STRENGTH;
    ssl_prefer_server_ciphers on;

    index index.html index.htm index.php;

    include /etc/nginx/custom_security.conf;
    
    client_max_body_size 10G; # set max upload size
    fastcgi_buffers 64 4K;

    location / {

            try_files $uri $uri/ index.php;
    }

    location ~ ^(.+?\.php)(/.*)?$ {
            try_files $1 = 404;

            include /etc/nginx/fastcgi.conf;
            fastcgi_param SCRIPT_FILENAME $document_root$1;
            fastcgi_param PATH_INFO $2;
            fastcgi_param HTTPS on;
            fastcgi_pass <?php echo $config['user'].'-';?>php-handler;
    }

    
}