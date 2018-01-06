rm -R /homeserverBackup/*  
rm /homeserverBackup.tar
rm /homeserverBackup.tar.gz
mkdir /homeserverBackup
mkdir /homeserverBackup/db
mkdir /homeserverBackup/webapp
mkdir /homeserverBackup/user
mysqlhotcopy --allowold homeserver /homeserverBackup/db/
rm /homeserverBackup/db/homeserver/udpcommandlog*
rm /homeserverBackup/db/homeserver/udpdatalog*
rm /homeserverBackup/db/homeserver/udphelper*
rm /homeserverBackup/db/homeserver/trace*
rm /homeserverBackup/db/homeserver/lastreceived*
cp -R /var/www/homeserver/webapp/*.webapp /homeserverBackup/webapp/
cp -R /var/www/homeserver/user/* /homeserverBackup/user/
#cp -R /var/www/homeserver/ /homeserverBackup/
tar -cf /homeserverBackup.tar /homeserverBackup/
gzip /homeserverBackup.tar
php /var/www/homeserver/editOnlineBackup.php backup $1
