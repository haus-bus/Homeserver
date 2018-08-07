<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($submitted==1)
{
	if ($suntimes==1)
	{
  	QUERY("delete from basicConfig where paramKey = 'offsetSunrise' or paramKey = 'offsetSunset' limit 2");
  	QUERY("INSERT into basicConfig (paramKey,paramValue) values('offsetSunrise','$offsetSunrise')");
   	QUERY("INSERT into basicConfig (paramKey,paramValue) values('offsetSunset','$offsetSunset')");
	}
	else
	{
    QUERY("DELETE from basicConfig where paramKey = 'locationZipCode' limit 1");
    QUERY("INSERT into basicConfig (paramKey,paramValue) values('locationZipCode','$locationZipCode')");
  
    QUERY("DELETE from basicConfig where paramKey = 'locationCountry' limit 1");
    QUERY("INSERT into basicConfig (paramKey,paramValue) values('locationCountry','$locationCountry')");
    
    QUERY("DELETE from basicConfig where paramKey = 'timeZone' limit 1");
    QUERY("INSERT into basicConfig (paramKey,paramValue) values('TimeZone','$timeZone')");
    
    $req = "http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20geo.places%20where%20text%3D%22" . $locationCountry . "%20" . $locationZipCode . "%22&format=xml";
    $api = simplexml_load_string ( utf8_encode ( file_get_contents ( $req, false, getStreamContext () ) ) );
    $latitude=$api->results->place->centroid->latitude;
    $longitude=$api->results->place->centroid->longitude;
  
    QUERY("DELETE from basicConfig where paramKey = 'latitude' or paramKey='longitude' limit 2");
    QUERY("INSERT into basicConfig (paramKey,paramValue) values('latitude','$latitude')");
    QUERY("INSERT into basicConfig (paramKey,paramValue) values('longitude','$longitude')");
  }

  
  $message="Einstellung wurde gespeichert.";
}

setupTreeAndContent("editLocation_design.html", $message);

$erg = QUERY("select paramValue,paramKey from basicConfig where paramKey = 'locationZipCode' or paramKey='locationCountry' or paramKey='TimeZone' or paramKey='latitude' or paramKey='longitude' or paramKey='offsetSunrise' or paramKey='offsetSunset' ");
while($row=mysqli_fetch_ROW($erg))
{
	 $vals[$row[1]]=$row[0];
}

$html = str_replace("%LOCATION_ZIP_CODE%", $vals['locationZipCode'], $html);
$html = str_replace("%LOCATION_COUNTRY%", $vals['locationCountry'], $html);
$html = str_replace("%TIME_ZONE%", $vals['TimeZone'], $html);
$html = str_replace("%LATITUDE%", $vals['latitude'], $html);
$html = str_replace("%LONGITUDE%", $vals['longitude'], $html);

$gmt = date('Z')/3600;
$html = str_replace("%GMT_OFFSET%", $gmt, $html);
$html = str_replace("%OFFSET_SUNRISE%", $vals['offsetSunrise'], $html);
$html = str_replace("%OFFSET_SUNSET%", $vals['offsetSunset'], $html);


$sunrise =  date_sunrise ( time(), SUNFUNCS_RET_STRING, $vals['latitude'], $vals['longitude'],ini_get("date.sunset_zenith"),$gmt);
$sunset =  date_sunset ( time(), SUNFUNCS_RET_STRING, $vals['latitude'], $vals['longitude'],ini_get("date.sunset_zenith"),$gmt);
$html = str_replace("%SUNRISE%", $sunrise, $html);
$html = str_replace("%SUNSET%", $sunset, $html);


show();

?>