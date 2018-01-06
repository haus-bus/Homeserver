<html>
<script type="text/javascript" src="homeserver.js"></script>
<script>
  #REGISTER_OBJECTS#
  
  function debugArea(message)
  {
  	 //document.getElementById("debugArea").value=message+"\n"+document.getElementById("debugArea").value;
  }
</script>
<body onload="init()" id="myBody">
<!--textarea rows=4 cols=80 id=debugArea></textarea-->

#SVG#

<table width="90%">
<tr>
<td align=center>
 <form>
 <table width=90%><tr><td>%BUTTONS%</td></tr></table>
 </form>
</td>
</tr>
</table>
<br><br><br><br>

<script>
// Kontextmenü verhindern
window.oncontextmenu = function(event) 
{
  if ((isMobile()&& event.which==1) || (!isMobile()&& event.which==3))
  {
    event.preventDefault();
    event.stopPropagation();
    return false;
  }
};

// Scheiß Textmarkierung verhindern
if (isMobile())
{
  document.ontouchend = function(event)
  {
    window.getSelection().removeAllRanges();
  }
  
  var el = document.getElementById("myBody"), s = el.style;
  s.userSelect = "none";
  s.webkitUserSelect = "none";
  s.MozUserSelect = "none";
  el.setAttribute("unselectable", "on"); // For IE and Opera

}
</script>
</body>
</html>