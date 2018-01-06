var count=0;
function blink()
{
	count++;
	if (count==2) count=0;
	
  var stern_style = document.getElementById('birne').style;
	if (count==0) stern_style.setProperty('fill','yellow');
	else stern_style.setProperty('fill','white');
	setTimeout("blink()",1000);
}
