rm -R /homeserverRestore
mkdir /homeserverRestore
cp /var/www/homeserver/restore.tar.gz /homeserverRestore
gzip -d /homeserverRestore/restore.tar.gz
tar -xvf /homeserverRestore/restore.tar -C /homeserverRestore
cp -R /homeserverRestore/homeserverBackup/user/* /var/www/homeserver/user/
cp -R /homeserverRestore/homeserverBackup/webapp/* /var/www/homeserver/webapp/
/etc/init.d/mysql stop
cp -R /homeserverRestore/homeserverBackup/db/homeserver/* /var/lib/mysql/homeserver/
myisamchk -r -f /var/lib/mysql/homeserver/*.MYI
/etc/init.d/mysql start
/etc/init.d/udpWorker restart


