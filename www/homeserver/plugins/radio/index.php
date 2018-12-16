<?php

if ($_POST["function"]=="Play") exec("mpc play");
else if ($_POST["function"]=="Stop") exec("mpc stop");
else if ($_POST["function"]=="Next") exec("mpc next");
else if ($_POST["function"]=="Previous") exec("mpc prev");
else if ($_POST["function"]=="updateStreams")
{
	 exec("mpc clear");
	 $newStreams = $_POST["streams"];
	 $urls = explode("\n",$newStreams);
	 foreach((array)$urls as $url)
	 {
	 	  $url = trim($url);
	 	  if (strlen($url)>5) $erg = exec("mpc add ".$url);
	 }
	 
	 file_put_contents("channels.txt",$newStreams);
}

echo "<html>";
echo '<head><link rel="StyleSheet" href="/homeserver/css/main.css" type="text/css" />';
echo '<body><div class="contentWrap"  id="content" style="margin-right:16px;">';

echo "<br><table width=95% align=center><tr><td>";
echo "<b>Raspberry Webstream Player</b><hr>";

$erg = exec("mpc current");
echo "Aktuell gespielter Song: $erg <br><br>";
echo "<form action='index.php' method='POST'>";
echo "
<input type=submit name=function value='Play'> 
<input type=submit name=function value='Stop'> 
<input type=submit name=function value='Next'> 
<input type=submit name=function value='Previous'> 
";
echo "</form><br>";
echo "<b>Aktuelle installierte Streams:</b><hr><br>";

echo "<form action=index.php method='POST'><input type=hidden name='function' value='updateStreams'>";
$content = file_get_contents("channels.txt");
echo "<textarea name=streams rows=20 cols=100>$content</textarea><br>";
echo "<input type=submit value='Streams aktualisieren'></form>";



echo "</td></tr></table>";

?>  