<?php

if (!file_exists("/var/www/homeserver/user/myUserPlugin.php"))
{
	echo "installing user/myUserPlugin.php ... \n";
	exec ("cp /var/www/homeserver/dummyMyUserPlugin.php /var/www/homeserver/user/myUserPlugin.php");
	exec ("chmod 777 /var/www/homeserver/user/myUserPlugin.php");
}
else echo "user/myUserPlugin.php already present ... \n";

if (!file_exists("/etc/init.d/udpClientServer"))
{
	echo "activating udpClientServer init script ... \n";
  $init = file_get_contents("/etc/init.d/udpWorker");
  $init = str_replace("udpWorker","udpClientServer",$init);
  file_put_contents("/etc/init.d/udpClientServer",$init);
  exec("chmod 777 /etc/init.d/udpClientServer");
}
else echo "udpClientServer init script already active \n"; 

$output = shell_exec('crontab -l');
if(strpos($output,"udpClientServer")===FALSE)
{
	echo "activating udpClientServer cronJob ... \n";
  file_put_contents('/tmp/crontab.txt', $output."* * * * * ps ax | grep 'udpClientServer' | grep -v 'grep' || /etc/init.d/udpClientServer start".PHP_EOL);
  exec('crontab /tmp/crontab.txt');
}
else echo "udpClientServer cronJob already active \n";

if(strpos($output,"udpClientTrigger")===FALSE)
{
	echo "activating udpClientServer timeTrigger ... \n";
  file_put_contents('/tmp/crontab.txt', $output."* * * * * /var/www/homeserver/udpClientTrigger.sh".PHP_EOL);
  exec('crontab /tmp/crontab.txt');
}
else echo "udpClientServer timeTrigger already active \n";

exec ("chmod 777 /var/www/homeserver/udpClientTrigger.sh");


?>


