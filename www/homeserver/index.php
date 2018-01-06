<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($tree=="")
{
	die("<html>
	<frameset cols='300,*,0'>
	  <frame src='index.php?tree=1' frameborder=1 marginwidth=0 marginheight=0 name=tree>
    <frame src='welcome.php' frameborder=1 name='main' marginwidth=0 marginheight=0>
    <frame src='empty.php' name='dummy' marginwidth=0 marginheight=0>
	</frameset>
	</html>");
}
else
{
  debugScript("vor setupTree");

  setupTreeAndContent();
  debugScript("nach setupTree");
  show();
}

?>