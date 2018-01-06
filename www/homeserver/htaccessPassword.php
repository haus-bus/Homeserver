<?php
include ($_SERVER ["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($submitted == 1)
{
	if (file_exists($_SERVER ["DOCUMENT_ROOT"] . "/homeserver/.htaccess")) unlink($_SERVER ["DOCUMENT_ROOT"] . "/homeserver/.htaccess");
	
	if (file_exists($_SERVER ["DOCUMENT_ROOT"] . "/homeserver/.htaccess")) $message="Achtung!<br>Die .htaccess Datei existiert bereits und konnte nicht geschrieben werden.<br>Bitte einmal per SSH einloggen und folgendes ausführen: sudo rm /var/www/homeserver/.htaccess <br><br>Die Datei wird durch dieses Skript anschließend wiederhergestellt!";
	else
	{
  	$cryptedPass = crypt($pass);
  	file_put_contents($_SERVER ["DOCUMENT_ROOT"] . "/homeserver/.htpasswd","homeserver:$cryptedPass");

    $content="php_value error_reporting 7125\n";
    $content.="php_value upload_max_filesize 16M\n";
    $content.="php_value post_max_size 16M\n";
  	
  	if ($umfang>0)
  	{
  		$content.="AuthUserFile ".$_SERVER ["DOCUMENT_ROOT"] . "/homeserver/.htpasswd\n";
      $content.="AuthName 'Homeserver Login'\n";
      $content.="AuthType Basic\n";
      $content.="require user homeserver\n";
    }
    
    if ($umfang==1)
    {
      $content.="order deny,allow\n";
      $content.="deny from all\n";
      $content.="allow from 192.168\n";
      $content.="satisfy any\n";
  	}
  	
    file_put_contents($_SERVER ["DOCUMENT_ROOT"] . "/homeserver/.htaccess",$content);
  	
  	QUERY("UPDATE basicConfig set paramValue='$umfang' where paramKey='htaccessType' limit 1");
  	QUERY("UPDATE basicConfig set paramValue='$pass' where paramKey='htaccessPass' limit 1");
  	
  
    $message = " Einstellung wurde gespeichert.";
  }
}

setupTreeAndContent ( "htaccessPassword_design.html", $message );

$erg = QUERY("select paramValue from basicConfig where paramKey='htaccessType'");
if ($row=MYSQL_FETCH_ROW($erg)) $value=$row[0];
else QUERY("INSERT into basicConfig (paramKey,paramValue) values('htaccessType','0')");

if ($value=="1")  $html = str_replace("%EINS_CHECKED%","checked",$html);
else if ($value=="2")  $html = str_replace("%ZWEI_CHECKED%","checked",$html);
else $html = str_replace("%NULL_CHECKED%","checked",$html);

$html = str_replace("%NULL_CHECKED%","",$html);
$html = str_replace("%EINS_CHECKED%","",$html);
$html = str_replace("%ZWEI_CHECKED%","",$html);

$erg = QUERY("select paramValue from basicConfig where paramKey='htaccessPass'");
if ($row=MYSQL_FETCH_ROW($erg)) $pass=$row[0];
else QUERY("INSERT into basicConfig (paramKey,paramValue) values('htaccessPass','')");

$html = str_replace("%PASS%",$pass,$html);

show ();

?>