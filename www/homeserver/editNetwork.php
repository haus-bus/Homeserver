<?php
include ($_SERVER ["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($submitted == 1)
{
  $message = "";
  if ($proxyIp != '' && ! filter_var ( $proxyIp, FILTER_VALIDATE_IP ) && ! filter_var ( $proxyIp, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED ))
  {
    $message .= "Proxy-IP ist ung�ltig!<br>";
  } else
  {
    if ($proxyIp == '') $proxyPort = '';
    else if ($proxyPort == '') $proxyPort = '80';
    MYSQL_QUERY ( "DELETE from basicConfig where paramKey = 'proxy' limit 1" ) or die ( MYSQL_ERROR () );
    MYSQL_QUERY ( "INSERT into basicConfig (paramKey,paramValue) values('proxy','$proxyIp')" ) or die ( MYSQL_ERROR () );
    MYSQL_QUERY ( "DELETE from basicConfig where paramKey = 'proxyPort' limit 1" ) or die ( MYSQL_ERROR () );
    MYSQL_QUERY ( "INSERT into basicConfig (paramKey,paramValue) values('proxyPort','$proxyPort')" ) or die ( MYSQL_ERROR () );
  }
  if ($networkIp != '' && ! filter_var ( $networkIp, FILTER_VALIDATE_IP ))
  {
    $message .= "Netzwerk-IP ist ung�ltig!<br>";
  } else
  {
    MYSQL_QUERY ( "DELETE from basicConfig where paramKey = 'networkIp' limit 1" ) or die ( MYSQL_ERROR () );
    MYSQL_QUERY ( "INSERT into basicConfig (paramKey,paramValue) values('networkIp','$networkIp')" ) or die ( MYSQL_ERROR () );
  }
  if (! $message)
    $message = " Einstellung wurde gespeichert.";
}

setupTreeAndContent ( "editNetwork_design.html", $message );

$erg = MYSQL_QUERY ( "select paramValue from basicConfig where paramKey = 'proxy' limit 1" ) or die ( MYSQL_ERROR () );
if ($row = MYSQL_FETCH_ROW ( $erg ))
  $myProxy = $row [0];
$html = str_replace ( "%PROXY%", $myProxy, $html );

$erg = MYSQL_QUERY ( "select paramValue from basicConfig where paramKey = 'proxyPort' limit 1" ) or die ( MYSQL_ERROR () );
if ($row = MYSQL_FETCH_ROW ( $erg ))
  $myProxyPort = $row [0];
$html = str_replace ( "%PROXY_PORT%", $myProxyPort, $html );

$erg = MYSQL_QUERY ( "select paramValue from basicConfig where paramKey = 'networkIp' limit 1" ) or die ( MYSQL_ERROR () );
if ($row = MYSQL_FETCH_ROW ( $erg ))
  $myNetworkIp = $row [0];
$html = str_replace ( "%NETWORK_IP%", $myNetworkIp, $html );

show ();

?>