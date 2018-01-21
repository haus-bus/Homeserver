rm -R /homeserverBackup/*  
rm /homeserverBackup.tar
rm /homeserverBackup.tar.gz
mkdir /homeserverBackup
mkdir /homeserverBackup/db
mkdir /homeserverBackup/webapp
mkdir /homeserverBackup/user
php /var/www/homeserver/homeserverBackup.php
cp -R /var/www/homeserver/webapp/*.webapp /homeserverBackup/webapp/
cp -R /var/www/homeserver/user/* /homeserverBackup/user/
tar -cf /homeserverBackup.tar /homeserverBackup/
gzip /homeserverBackup.tar
php /var/www/homeserver/editOnlineBackup.php backup $1
