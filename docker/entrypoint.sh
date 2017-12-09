#!/bin/bash

# These are obsoleted by docker compose these days
# POSTGRES_PORT=$POSTGRES_PORT_5432_TCP_PORT
# POSTGRES_HOST=$POSTGRES_PORT_5432_TCP_ADDR
export POSTGRES_PORT=5432
export POSTGRES_HOST=postgres

echo "$USERNAME: Using DB $DB_NAME with user $DB_USER on $POSTGRES_HOST:$POSTGRES_PORT"

# Add local user
# Either use the LOCAL_USER_ID if passed in at runtime or
# fallback

USER_ID=${LOCAL_USER_ID:-9001}

echo "Starting with UID : $USER_ID"
useradd --shell /bin/bash -u $USER_ID -o -c "" -m someuser
export HOME=/home/user

cd /opt/installtree
echo "Ensuring directories exist"
mkdir -p vendor
mkdir -p data/cache
mkdir /run/php
for i in cache logs sessions; do mkdir -p /opt/installtree/var/$i; done
chmod +x /opt/installtree/init.sh
sync
sleep 1
echo "Running init script"
/opt/installtree/init.sh
echo "Changing ownership for someuser"
chown -R someuser:someuser /opt/installtree

if [ -x /code/.git ]; then
  echo "Haven't implemented importing developer code yet"
  exit -1
fi

echo "Populating supervisor config"
cat > /etc/supervisor/conf.d/supervisord.conf <<EOF
[supervisord]
nodaemon=true

# [program:rsyslog]
# command=/usr/sbin/rsyslogd -n -c3

[program:php]
command=/usr/sbin/php-fpm7.1 -c /etc/php/7.1/fpm -F

[program:cron]
command=/usr/sbin/cron

[program:nginx]
command=/usr/sbin/nginx
stdout_events_enabled=true
stderr_events_enabled=true
EOF

echo "Populating nginx config"
echo "daemon off;" >> /etc/nginx/nginx.conf
sed -e's/www-data/someuser/' -i /etc/nginx/nginx.conf

#http://symfony.com/doc/current/setup/web_server_configuration.html
cat > /etc/nginx/sites-available/default <<EOF
server {
    listen 8000;
    root /opt/installtree;

    location / {
        # try to serve file directly, fallback to app.php
        try_files \$uri =404;
    }

EOF

cat >> /etc/nginx/sites-available/default <<EOF
    location ~ [^/]\.php(/|$) {
        fastcgi_split_path_info ^(.+?\.php)(/.*)$;
        fastcgi_pass unix:/run/php/php7.1-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$request_filename;
        fastcgi_param DOCUMENT_ROOT \$realpath_root;
        fastcgi_param HTTP_PROXY "";
        fastcgi_index index.php;
        internal;
    }

    error_log /var/log/nginx/rff_error.log;
    access_log /var/log/nginx/rff_access.log;
}
EOF

echo "Configuring PHP"
sed -e's/www-data/someuser/' -i /etc/php/7.1/fpm/pool.d/www.conf
#FIXME: Log rotation


DOCKER_HOST_IP=`/sbin/ip route|awk '/default/ { print $3 }'`
echo "Configuring symfony for host $DOCKER_HOST_IP"
sed -e"s/trusted_proxies: \~/trusted_proxies: [$DOCKER_HOST_IP, 127.0.0.1]/" -i /opt/installtree/app/config/config.yml

echo "Installing cron"
echo '10,30,50 * * * * someuser /opt/installtree/crontask.sh >> /var/log/crontask.log 2>&1' > /etc/cron.d/crontask
echo '@reboot someuser /opt/installtree/crontask.sh >> /var/log/crontask.log 2>&1' >> /etc/cron.d/crontask
touch /var/log/cron.log
touch /var/log/crontask.log
chown someuser /var/log/crontask.log
sed 's,#cd,cd /opt/installtree,' -i /opt/installtree/crontask.sh

echo "Starting supervisor"
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf # &&/taillogs.sh
