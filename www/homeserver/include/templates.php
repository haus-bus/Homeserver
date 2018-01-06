<?php

function loadTemplate($name)
{
   global $showTemplateName;
   global $showTemplate;
   global $setTemplate;

   if ($setTemplate!="") $name=$setTemplate;
   else $name="templates/".$name;

   if ($showTemplateName==1) die($name);
   
   $html=file_get_contents($name);
   if ($showTemplate==1) die($html);

   return $html;
}

// Liefert den angegebenen Tag im Format %TAGNAME% und ersetzt den Bereich im HTML durch das Keyword
function getTag($tag, &$html)
{
	/*$pattern="#<$tag>(.*?)</$tag>#";
	
	$found = preg_match ( $pattern , $html, $matches);
	if ($found!==FALSE)
	{
		  $result = $matches[1];
		  $html = preg_replace( $pattern , $tag, $html); 
		  return $result;
	}
	return "";
	*/
	
  $pos = strpos($html, "<$tag>");
  if ($pos === FALSE) return ""; //die("Tag $tag nicht gefunden -> ".htmlentities($html));
  $pos2 = strpos($html, "</$tag>");
  if ($pos2 === FALSE) return ""; //die("Endetag $tag nicht gefunden -> ".htmlentities($html));
  
  $result = substr($html, $pos +strlen($tag) + 2, $pos2 - $pos -strlen($tag) - 2);
  $front = substr($html, 0, $pos);
  $ende = substr($html, $pos2 +strlen($tag) + 3, strlen($html) - $pos2 -strlen($tag) - 3);
  $html = $front.$tag.$ende;
 
  return $result;
}

// Entfernt den angegebenen Tag im Format %TAGNAME%
function removeTag($tag, &$html)
{
  getTag($tag,$html);
  $html = str_replace ( $tag, "", $html );
  return $html;
}

// Bei optionalen Teilen, kann man hiermit den Tag entfernen, wenn er gewählt werden soll
function chooseTag($tag, &$html)
{
  $content=getTag($tag,$html);
  $html = str_replace ( $tag, $content, $html );
  return $html;
}

function showErrorLink()
{
  showMessage("Fehler","Der gewählte Link ist ungültig. Sollte der Fehler dauerhaft bestehen, schicken Sie bitte eine Email an info@eventing-network.de","","");
}

?>