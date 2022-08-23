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


box_out "Kiwire v3 - Installer - Built 5"

ubuntu_version=$(lsb_release -r | awk '{print $2}')

if [ "$ubuntu_version" != "20.04" ]; then


  clear

  echo "This installer only works for Ubuntu 20.04"
  echo "Terminated"

  exit


fi


box_out "Check for any Ubuntu update.."

apt update -y | apt upgrade -y

sleep 1


box_out "Installing repositories.."

apt-key adv --keyserver keyserver.ubuntu.com --recv-keys B9316A7BC7917B12

add-apt-repository 'deb http://ppa.launchpad.net/chris-lea/redis-server/ubuntu focal main'


box_out "Installing server softwares.."

apt install -y nginx php-fpm php-pear freeradius freeradius-rest freeradius-utils net-tools tcpdump redis mariadb-server dos2unix

systemctl enable --now nginx redis-server php7.4-fpm freeradius mariadb


apt install php-radius php-ssh2 php-ldap php-msgpack php-bz2 php-sqlite3 php-mbstring php-json php-curl php-bcmath php-gd php-mysql php-zip php-redis php-snmp tmux rsync percona-toolkit glances tuned zip -y


# enable freeradius module

ln -s /etc/freeradius/3.0/mods-available/raw /etc/freeradius/3.0/mods-enabled/
ln -s /etc/freeradius/3.0/mods-available/rest /etc/freeradius/3.0/mods-enabled/
ln -s /etc/freeradius/3.0/sites-available/dynamic-clients /etc/freeradius/3.0/sites-enabled/
ln -s /etc/freeradius/3.0/sites-available/default /etc/freeradius/3.0/sites-enabled/

box_out "Change performance profile.."

tuned-adm profile throughput-performance


box_out "Remove SWAP partition to avoid database performance issue.."

sed -i '/swap.img/d' /etc/fstab


box_out "Apply default setting for performance.."

tar xfz package/kiwire-config-ubuntu.tgz -C /


box_out "Installing Kiwire core system.."

mkdir -p /var/www/

tar xfz package/kiwire-core.tgz -C /var/www/


sleep 2


mysql -u root -e "create database kiwire"


mysql kiwire < package/kiwire-schema.sql

mysql kiwire < package/kiwire-basic.sql


# by default, ubuntu dont have 127.0.0.1 created

mysql -u root -e "CREATE USER 'root'@'127.0.0.1' IDENTIFIED BY ''"

mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1'"

mysql -u root -e "FLUSH PRIVILEGES"

mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root mysql


systemctl restart mariadb


box_out "Enable service to be run during boot time.."

chmod -x /var/www/kiwire/system/services/*

cp -rf /var/www/kiwire/system/services/* /usr/lib/systemd/system/


sleep 2


# update daemon to remove private /tmp directory

sed -i 's/PrivateTmp=yes/PrivateTmp=no/' /usr/lib/systemd/system/redis-server.service
sed -i 's/PrivateTmp=yes/PrivateTmp=no/' /usr/lib/systemd/system/systemd-timesyncd.service
sed -i 's/PrivateTmp=yes/PrivateTmp=no/' /usr/lib/systemd/system/systemd-resolved.service
sed -i 's/PrivateTmp=yes/PrivateTmp=no/' /usr/lib/systemd/system/systemd-logind.service

systemctl daemon-reload

systemctl enable --now kiwire_integration kiwire_service



box_out "Change timezone to UTC.."

rm -rf /etc/localtime

ln -sf /usr/share/zoneinfo/UTC /etc/localtime



box_out "Open port for http, https, and radius communications.."

ufw allow ssh
ufw allow http
ufw allow https
ufw allow radius
ufw allow radius-acct

ufw --force enable

# update some config such installed time for default

sleep 2

/usr/bin/php config.php


box_out "Change file permission to allow appropriate service running.."

chown www-data:www-data -R /var/lib/php
chown www-data:www-data -R /var/www/kiwire
chown freerad:freerad -R /etc/freeradius/

chmod 755 -R /var/www/kiwire
chmod 755 -R /var/lib/php


dos2unix /var/www/kiwire/system/schedule/scheduler.cron
crontab /var/www/kiwire/system/schedule/scheduler.cron


box_out "Complete. Reboot.."

sleep 3

reboot
