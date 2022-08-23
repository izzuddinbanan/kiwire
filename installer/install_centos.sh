#!/usr/bin/env bash


function box_out()
{
  clear

  local s=("$@") b w
  for l in "${s[@]}"; do
    ((w<${#l})) && { b="$l"; w="${#l}"; }
  done
  tput setaf 2
  echo " -${b//?/-}-
| ${b//?/ } |"
  for l in "${s[@]}"; do
    printf '| %s%*s%s |\n' "$(tput setaf 2)" "-$w" "$l" "$(tput setaf 2)"
  done
  echo "| ${b//?/ } |
 -${b//?/-}-"
  tput sgr 0

  sleep 2
}


setenforce 0


box_out "Kiwire v3 - Installer - Built 2021"

centos_version=$(cat /etc/centos-release | awk '{print $4}' | cut -d '.' -f 1)

if [ "$centos_version" != "8" ]; then


  clear

  echo "This installer only works for CentOS 8"
  echo "Terminated"

  exit


fi


box_out "Remove automatic update package if installed.."

dnf remove dnf-automatic -y


box_out "Installing basic tools.."

dnf update -y

sleep 3

dnf install net-snmp python2 epel-release vim nmap-ncat net-tools tuned dos2unix curl -y

dnf module install perl:5.26 -y


box_out "Update package repository list.."

cp package/Kiwire.repo /etc/yum.repos.d/

dnf update -y


box_out "Installing RADIUS packages and configuration.."

dnf install package/freeradius/freeradius*.rpm  -y

tar xfz package/kiwire-radius.tgz -C /

echo '
raw {

}
' > /etc/raddb/mods-available/raw

ln -s /etc/raddb/mods-available/rest /etc/raddb/mods-enabled/
ln -s /etc/raddb/mods-available/raw /etc/raddb/mods-enabled/
ln -s /etc/raddb/sites-available/dynamic-clients /etc/raddb/sites-enabled/
ln -s /etc/raddb/sites-available/status /etc/raddb/sites-enabled/


box_out "Installing PHP and REDIS package.."

dnf module reset php redis -y

dnf module install php:remi-7.3 redis:remi-5.0 -y

dnf install php-pecl-radius php-pecl-swoole4 php-pecl-ssh2 php-ldap php-bcmath php-gd php-mysqlnd php-pecl-zip php-pecl-redis5 php-snmp tmux rsync -y


box_out "Installing NGINX package.."

#dnf install nginx bashtop -y
dnf install nginx -y


box_out "Installing Mariadb Server and Percona Toolkit package.."

dnf install MariaDB-server screen htop -y

dnf install package/percona-toolkit-3.1.0-2.el8.x86_64.rpm -y


box_out "Change performance profile.."

tuned-adm profile throughput-performance


box_out "Remove SWAP partition to avoid database performance issue.."

sed -i '/-swap/d' /etc/fstab


box_out "Apply default setting for performance.."

tar xfz package/kiwire-config.tgz -C /


box_out "Installing Kiwire core system.."

systemctl start mariadb


mkdir -p /var/www/

tar xfz package/kiwire-core.tgz -C /var/www/


sleep 2


mysql -u root -e "create database kiwire"


mysql kiwire < package/kiwire-schema.sql

mysql kiwire < package/kiwire-basic.sql


mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root mysql


box_out "Enable service to be run during boot time.."

chmod -x /var/www/kiwire/system/services/*

cp -rf /var/www/kiwire/system/services/* /usr/lib/systemd/system/


sleep 2


# update daemon to remove private /tmp directory

sed -i 's/PrivateTmp=true/PrivateTmp=false/' /usr/lib/systemd/system/radiusd.service
sed -i 's/PrivateTmp=true/PrivateTmp=false/' /usr/lib/systemd/system/mariadb.service
sed -i 's/PrivateTmp=true/PrivateTmp=false/' /usr/lib/systemd/system/php-fpm.service
sed -i 's/PrivateTmp=true/PrivateTmp=false/' /usr/lib/systemd/system/redis.service
sed -i 's/PrivateTmp=yes/PrivateTmp=no/' /usr/lib/systemd/system/chronyd.service


systemctl daemon-reload

systemctl enable mariadb radiusd redis php-fpm nginx kiwire_integration kiwire_service



box_out "Change timezone to UTC.."

rm -rf /etc/localtime

ln -sf /usr/share/zoneinfo/UTC /etc/localtime



box_out "Open port for http, https, and radius communication.."

firewall-cmd --permanent --add-service={http,https,radius}


# update some config such installed time for default

systemctl start redis mariadb

sleep 5


mkdir -p /var/www/kiwire/server/custom/default
/usr/bin/php config.php


box_out "Change file permission to allow appropriate service running.."


chown nginx:nginx -R /var/lib/php
chown nginx:nginx -R /var/www/kiwire/logs
chown nginx:nginx -R /var/www/kiwire/server/custom
chown radiusd:radiusd -R /etc/raddb
chown nginx:nginx -R /var/www/kiwire/server/temp

chmod 755 -R /var/www/kiwire/logs
chmod 755 -R /var/www/kiwire/server/custom
chmod 755 -R /var/lib/php
chown 755 -R /var/www/kiwire/server/temp


dos2unix /var/www/kiwire/system/schedule/scheduler.cron
crontab /var/www/kiwire/system/schedule/scheduler.cron

box_out "Install git .."

dnf install git -y


box_out "Complete. Reboot.."

sleep 3

reboot
