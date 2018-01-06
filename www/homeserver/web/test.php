<html>
<script>
	function switchElement(id)
	{
		 if (document.getElementById(id).style.width=="100px")
		 {
		   document.getElementById(id).style.width="300px";
		   document.getElementById(id).style.height="300px";
		   document.getElementById(id+"1").style.visibility="visible";
		 }
		 else
		 {
		   document.getElementById(id).style.width="100px";
		   document.getElementById(id).style.height="100px";
		   document.getElementById(id+"1").style.visibility="hidden";
		 }
	}
</script>
<body>
	<br><br><br>
	
	<div style="width:100px;height:100px;background-image:url('verlauf_gross.gif');" id="taster" onclick="switchElement('taster')">
		<div style="width:45%;height:45%;background-color:blue;visibility:hidden" id="taster1" onclick="alert('huhu')"></div>
	</div>
</body>
</html>