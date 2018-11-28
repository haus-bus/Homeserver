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
	if ($networkPort == '') $networkPort = 9;
    QUERY ( "DELETE from basicConfig where paramKey = 'networkPort' limit 1" );
    QUERY ( "INSERT into basicConfig (paramKey,paramValue) values('networkPort','$networkPort')" );	
	if ($networkMask == '') $networkMask = '255.255.255.0';
    if ($networkMask != '' && ! filter_var ( $networkMask, FILTER_VALIDATE_IP ))
    {
	  $message .= "Netzwerk-IP ist ungültig!<br>";
    }
	else
	{
	  QUERY ( "DELETE from basicConfig where paramKey = 'networkMask' limit 1" );
      QUERY ( "INSERT into basicConfig (paramKey,paramValue) values('networkMask','$networkMask')" );	
	}		
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

$erg = QUERY ( "select paramValue from basicConfig where paramKey = 'networkPort' limit 1" );
if ($row = mysqli_fetch_ROW ( $erg ))
  $myNetworkPort = $row [0];
$html = str_replace ( "%NETWORK_PORT%", $myNetworkPort, $html );

$erg = QUERY ( "select paramValue from basicConfig where paramKey = 'networkMask' limit 1" );
if ($row = mysqli_fetch_ROW ( $erg ))
  $myNetworkMask = $row [0];
$html = str_replace ( "%NETWORK_MASK%", $myNetworkMask, $html );

show ();

?>