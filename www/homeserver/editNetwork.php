<?php
include ($_SERVER ["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($submitted == 1)
{
  $message = "";
  if ($proxyIp != '' && ! filter_var ( $proxyIp, FILTER_VALIDATE_IP ) && ! filter_var ( $proxyIp, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED ))
  {
    $message .= "Proxy-IP ist ungültig!<br>";
  } else
  {
    if ($proxyIp == '') $proxyPort = '';
    else if ($proxyPort == '') $proxyPort = '80';
    QUERY ( "DELETE from basicConfig where paramKey = 'proxy' limit 1" );
    QUERY ( "INSERT into basicConfig (paramKey,paramValue) values('proxy','$proxyIp')" );
    QUERY ( "DELETE from basicConfig where paramKey = 'proxyPort' limit 1" );
    QUERY ( "INSERT into basicConfig (paramKey,paramValue) values('proxyPort','$proxyPort')" );
  }
  if ($networkIp != '' && ! filter_var ( $networkIp, FILTER_VALIDATE_IP ))
  {
    $message .= "Netzwerk-IP ist ungültig!<br>";
  } else
  {
    QUERY ( "DELETE from basicConfig where paramKey = 'networkIp' limit 1" );
    QUERY ( "INSERT into basicConfig (paramKey,paramValue) values('networkIp','$networkIp')" );
  }
  if (! $message)
    $message = " Einstellung wurde gespeichert.";
}

setupTreeAndContent ( "editNetwork_design.html", $message );

$erg = QUERY ( "select paramValue from basicConfig where paramKey = 'proxy' limit 1" );
if ($row = mysqli_fetch_ROW ( $erg ))
  $myProxy = $row [0];
$html = str_replace ( "%PROXY%", $myProxy, $html );

$erg = QUERY ( "select paramValue from basicConfig where paramKey = 'proxyPort' limit 1" );
if ($row = mysqli_fetch_ROW ( $erg ))
  $myProxyPort = $row [0];
$html = str_replace ( "%PROXY_PORT%", $myProxyPort, $html );

$erg = QUERY ( "select paramValue from basicConfig where paramKey = 'networkIp' limit 1" );
if ($row = mysqli_fetch_ROW ( $erg ))
  $myNetworkIp = $row [0];
$html = str_replace ( "%NETWORK_IP%", $myNetworkIp, $html );

show ();

?>