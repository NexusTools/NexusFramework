<?php
header("Content-Type: text/html; charset=utf8");
?><!doctype html>
<html><head><title>Nexus Framework Installer</title>
<style>
body {
	cursor: default;
	margin-top: 30px;
	background: #072951;
	font-family: arial, georgia;
	font-size: 90%;
}

.page {
	display: table;
	margin: 0 auto;
	padding: 15px;
	border: solid 2pt gray;
	border-radius: 15px;
	background-color: white;
	-webkit-box-shadow: 0px 0px 15px 0px rgba(50, 50, 50, 1);
	-moz-box-shadow: 0px 0px 15px 0px rgba(50, 50, 50, 1);
	box-shadow: 0px 0px 15px 0px rgba(50, 50, 50, 1);
}

.headbubble {
	text-decoration: none;
	color: #444;
	font-weight: bold;
	font-size: 140%;
	display: table;
	padding: 10px;
	padding-right: 30px;
	padding-left: 30px;
	border-radius: 15px;
	background-color: #f0f0f0;
	text-shadow: 2px 2px 5px #cfcfcf;
	filter: dropshadow(color=#cfcfcf, offx=2, offy=2);
	margin-bottom: 30px;
	
	transition: all 200ms ease;
	-moz-transition: all 200ms ease; /* Firefox 4 */
	-webkit-transition: all 200ms ease; /* Safari and Chrome */
	-o-transition: all 200ms ease; /* Opera */
}

.headbubble:hover {
	color: #666;
	background-color: #f5f5f5;
	text-shadow: 0px 0px 0px #cfcfcf;
	filter: none;
}

pre {
	width: 600px;
	max-height: 500px;
	overflow: auto;
}

* {
    outline: none;
}

table td {
	color: #555;
	font-size: 12px;
	height: 50%;
	margin: 0px;
	padding: 0px;
	font-family: arial, georgia;
}

groupbox table td input.button {
	width: 162px;
}

select {
	color: #555;
	width: 162px;
	border: solid 1pt #aaa;
	background: white;
	padding: 3px;
	
	transition: all 200ms ease;
	-moz-transition: all 200ms ease; /* Firefox 4 */
	-webkit-transition: all 200ms ease; /* Safari and Chrome */
	-o-transition: all 200ms ease; /* Opera */
	
	
}

select:focus,
select:hover {
	color: #333;
	border: solid 1pt #444;
}

input.button.disabled,
input.button.disabled:hover,
input.button.disabled:active {
	background: rgb(242,242,242);
	border: solid 1pt #ccc;
	color: #aaa;
}

input.button {
	color: #888;
	font-weight: bold;
	text-align: center;
	background: rgb(242,242,242);
	background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2YyZjJmMiIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjE2JSIgc3RvcC1jb2xvcj0iI2Q2ZDZkNiIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNlZmVmZWYiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
	background: -moz-linear-gradient(top,  rgba(242,242,242,1) 0%, rgba(214,214,214,1) 16%, rgba(239,239,239,1) 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(242,242,242,1)), color-stop(16%,rgba(214,214,214,1)), color-stop(100%,rgba(239,239,239,1)));
	background: -webkit-linear-gradient(top,  rgba(242,242,242,1) 0%,rgba(214,214,214,1) 16%,rgba(239,239,239,1) 100%);
	background: -o-linear-gradient(top,  rgba(242,242,242,1) 0%,rgba(214,214,214,1) 16%,rgba(239,239,239,1) 100%);
	background: -ms-linear-gradient(top,  rgba(242,242,242,1) 0%,rgba(214,214,214,1) 16%,rgba(239,239,239,1) 100%);
	background: linear-gradient(top,  rgba(242,242,242,1) 0%,rgba(214,214,214,1) 16%,rgba(239,239,239,1) 100%);
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f2f2f2', endColorstr='#efefef',GradientType=0 );

	transition: all 500ms ease;
	-moz-transition: all 500ms ease; /* Firefox 4 */
	-webkit-transition: all 500ms ease; /* Safari and Chrome */
	-o-transition: all 500ms ease; /* Opera */

	border: solid 1pt #888;
	padding: 4px;
	padding-right: 8px;
	padding-left: 8px;
}

input.button:hover {
	color: #666;
	background: rgb(242,242,242);
background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2YyZjJmMiIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjE0JSIgc3RvcC1jb2xvcj0iI2Q2ZDZkNiIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNlZGVkZWQiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
background: -moz-linear-gradient(top,  rgba(242,242,242,1) 0%, rgba(214,214,214,1) 14%, rgba(237,237,237,1) 100%);
background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(242,242,242,1)), color-stop(14%,rgba(214,214,214,1)), color-stop(100%,rgba(237,237,237,1)));
background: -webkit-linear-gradient(top,  rgba(242,242,242,1) 0%,rgba(214,214,214,1) 14%,rgba(237,237,237,1) 100%);
background: -o-linear-gradient(top,  rgba(242,242,242,1) 0%,rgba(214,214,214,1) 14%,rgba(237,237,237,1) 100%);
background: -ms-linear-gradient(top,  rgba(242,242,242,1) 0%,rgba(214,214,214,1) 14%,rgba(237,237,237,1) 100%);
background: linear-gradient(top,  rgba(242,242,242,1) 0%,rgba(214,214,214,1) 14%,rgba(237,237,237,1) 100%);
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f2f2f2', endColorstr='#ededed',GradientType=0 );
}

input.button:active {
	transition: all 200ms ease;
	-moz-transition: all 200ms ease; /* Firefox 4 */
	-webkit-transition: all 200ms ease; /* Safari and Chrome */
	-o-transition: all 200ms ease; /* Opera */
	color: #aaa;
	background: rgb(201,201,201);
background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2M5YzljOSIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjE2JSIgc3RvcC1jb2xvcj0iI2RiZGJkYiIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNlZGVkZWQiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
background: -moz-linear-gradient(top,  rgba(201,201,201,1) 0%, rgba(219,219,219,1) 16%, rgba(237,237,237,1) 100%);
background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(201,201,201,1)), color-stop(16%,rgba(219,219,219,1)), color-stop(100%,rgba(237,237,237,1)));
background: -webkit-linear-gradient(top,  rgba(201,201,201,1) 0%,rgba(219,219,219,1) 16%,rgba(237,237,237,1) 100%);
background: -o-linear-gradient(top,  rgba(201,201,201,1) 0%,rgba(219,219,219,1) 16%,rgba(237,237,237,1) 100%);
background: -ms-linear-gradient(top,  rgba(201,201,201,1) 0%,rgba(219,219,219,1) 16%,rgba(237,237,237,1) 100%);
background: linear-gradient(top,  rgba(201,201,201,1) 0%,rgba(219,219,219,1) 16%,rgba(237,237,237,1) 100%);
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#c9c9c9', endColorstr='#ededed',GradientType=0 );

}

input.text.disabled,
input.text.disabled:hover,
input.text.disabled:focus,
select.disabled,
select.disabled:hover,
select.disabled:focus {
	cursor: default;
	border: solid 1pt #ccc;
	color: #aaa;
}

input.text {
	border: solid 1pt #aaa;
	border-top: solid 1pt #777;
	color: #555;
	padding: 4px;
	background-color: white;
	
	transition: all 200ms ease;
	-moz-transition: all 200ms ease; /* Firefox 4 */
	-webkit-transition: all 200ms ease; /* Safari and Chrome */
	-o-transition: all 200ms ease; /* Opera */
}

input.text:hover {
	color: #5a5a5a;
	border: solid 1pt #666;
}

input.text:focus {
	color: #333;
	border: solid 1pt #444;
	-webkit-box-shadow: 0px -1px 0px 0px rgba(0, 0, 0, 0.4);
	-moz-box-shadow: 0px -1px 0px 0px rgba(0, 0, 0, 0.4);
	box-shadow: 0px -1px 0px 0px rgba(0, 0, 0, 0.4);
}

input,
select {
	margin-top: -1px;
}

bubble,
groupbox label {
	
	margin-left: 5px;
	margin-bottom: 5px;
	display: table;
	padding: 3px;
	padding-left: 6px;
	padding-right: 6px;
	border: solid 1pt lightGray;
	border-radius: 4px;
	background-color: white;
}

groupbox table {
	width: 100%;
}

groupbox {
	width: 100%;
	text-align: left;
	margin-top: 30px;
	display: table;
	border: solid 1pt lightGray;
	border-radius: 5px;
	padding: 5px;
}

groupbox label {
	color: #444;
	margin-top: -20px;
	font-weight: 400;
	background: rgb(255,255,255);
	background: -moz-linear-gradient(top,  rgba(255,255,255,1) 0%, rgba(249,249,249,1) 12%, rgba(255,255,255,1) 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(255,255,255,1)), color-stop(12%,rgba(249,249,249,1)), color-stop(100%,rgba(255,255,255,1)));
	background: -webkit-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(249,249,249,1) 12%,rgba(255,255,255,1) 100%);
	background: -o-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(249,249,249,1) 12%,rgba(255,255,255,1) 100%);
	background: -ms-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(249,249,249,1) 12%,rgba(255,255,255,1) 100%);
	background: linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(249,249,249,1) 12%,rgba(255,255,255,1) 100%);
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#ffffff',GradientType=0 );
}

bubble {
	font-size: 9px;
	padding: 2px;
	cursor: pointer;
	text-decoration: none;
	margin-top: -18px;
	margin-right: 5px;
	color: inherit;
	display: table;
	float: right;
	transition: all 200ms ease;
	-moz-transition: all 200ms ease; /* Firefox 4 */
	-webkit-transition: all 200ms ease; /* Safari and Chrome */
	-o-transition: all 200ms ease; /* Opera */
}

bubble:hover {
	background-color: #f1f1f1;
}

tooltip {
	display: none;
	position: absolute;
	border: solid 1pt black;
	padding: 5px;
}

</style></head><body><div align="center" class="page">
<a class="headbubble" href="http://www.nexustools.net/">Nexus Tools<br />PHP Framework</a>
<?php
error_reporting(0);
if (!is_writable(INDEX_PATH)) {
?>
PHP Requires write permission to `<?php echo INDEX_PATH; ?>`.<br/>
This can be fixed automatically, please select an option.<br /><br />
<input class="button" type="submit" value="Fix using FTP" /><br />
<input class="button" type="submit" value="Fix using Host User Permissions" />
<?php } else { ?>
<tooltip for="admin_user">
</tooltip>
<form method="POST" action="/"><table style="text-align: center;"><tr><td valign="top">
	<groupbox><label>Administration</label><table>
		<tr><td style="padding-bottom: 0px;">Username</td><td style="padding-bottom: 0px;">Password</td></tr>
		<tr>
			<td><input id="admin_user" type="text" class="text" value="" /></td>
			<td><input type="text" class="text" value="" /></td>
		</tr>
		<tr><td style="padding-bottom: 0px;">Email</td></tr>
		<tr>
			<td><input type="text" class="text" /></td>
		</tr>
	</table></groupbox>

	<div style="display: none" id="advanced_left"><groupbox>
		<bubble title="Try to connect to the specified database.">Connect</bubble>
		<label>MySql Connection</label>
	<table>
		<tr><td style="padding-bottom: 0px;">Hostname</td><td style="padding-bottom: 0px;">Database</td></tr>
		<tr>
			<td><input type="text" class="text" value="localhost" /></td>
			<td><input type="text" class="text" value="" /></td>
		</tr>
		<tr>
			<td style="padding-bottom: 0px;">Username</td>
			<td style="padding-bottom: 0px;">Password</td>
		</tr>
		<tr>
			<td><input type="text" class="text" value="root" /></td>
			<td><input type="password" class="text" value="" /></td>
		</tr>
		
	</table></groupbox></div>
</td><td style="padding: 7px;"></td><td valign="top">

	<groupbox class="groupbox">
	<bubble>Preview</bubble>
	<label>Template</label>
	<table>
		<tr><td style="padding-bottom: 0px;">Title Format</td><td style="padding-bottom: 0px;">Default Page Name</td></tr>
		<tr>
			<td><input type="text" class="text" name="title" value="Website | %pagename%" /></td>
			<td><input type="text" class="text" name="title" value="Home" /></td>
		</tr>
		<tr><td style="padding-bottom: 0px;">Theme</td></tr>
		<tr>
			<td><select><option>Default</option></select></td>
		</tr>
	</table></groupbox>
	
	<div style="display: none" id="advanced_right">
	<groupbox><label>Backends</label>
	<table>
		<tr>
			<td style="padding-bottom: 0px;">Database</td>
			<td style="padding-bottom: 0px;">Account Manager</td>
		</tr>
		<tr>
			<td><select><?php
	if (class_exists("PDO")) {
		echo "<optgroup LABEL=\"PDO Drivers\">";
		$drivers = PDO::getAvailableDrivers();
		foreach ($drivers as $driver) {
			echo "<option";
			if ($driver == "sqlite")
				echo " selected";
			echo ">$driver</option>";
		}
		echo "</optgroup>";
	}
?>
			</select></td>
			<td><select>
				<option>None</option>
				<option>Database Table</option>
				<option>UAM</option>
			</select></td>
		</tr>
	</table></groupbox>
	<groupbox><label>Install Path</label>
	<table>
		<tr><td style="padding-bottom: 0px;">Directory</td><td style="padding-bottom: 0px;">Base URL</td></tr>
		<tr>
			<td><input label="" type="text" class="text disabled" name="title" value="<?php echo INDEX_PATH; ?>" disabled /></td>
			<td><input type="text" class="text disabled" name="title" value="<?php echo ROOT_URL; ?>" disabled /></td>
		</tr>
	</table></groupbox></div>
</td></tr>
<tr>
	<td valign="bottom">
		<input id="toggle_options" class="button" type="button" style="font-size: 10px" value="▼ Show Advanced ▼" />
	</td>
	<td colspan="3" style="padding-top: 10px;" align="right">
		<input class="button" type="button" value="Install Code" />
		<input class="button disabled" type="submit" value="Begin Install" disabled />
	</td>
</tr>
</table></form>
<?php } ?>
</div><script>
function hide_element(el){
	try{clearTimeout(el.opacityTimer);}catch(e){}
	if(!el.nHeight){
		el.opacity = 1;
		el.nHeight = el.offsetHeight + 50;
		el.height = el.nHeight;
		el.style.height = el.nHeight + "px";
	}
	
	if(el.style.overflow != "hidden")
		el.style.overflow = "hidden";
	
	if(el.height > 0) {
		el.height -= (el.height+2) / 3;
		
		if(el.height <= 0)
			el.style.display = "none";
		else {
			el.opacityTimer = setTimeout(function(){
				hide_element(el);
			}, 40);
			el.style.height = el.height + "px";
		}
	}
}

function show_element(el){
	try{clearTimeout(el.opacityTimer);}catch(e){}
	if(!el.nHeight){
		el.style.display = "";
		el.style.height = "";
		el.nHeight = el.offsetHeight + 30;
		el.height = 0;
		el.style.overflow = "hidden";
	}
	
	if(el.height < el.nHeight) {
		el.height += ((el.nHeight-el.height)+2) / 3;
		
		if(el.height > el.nHeight) {
			el.style.display = "";
			el.style.height = "";
			el.style.overflow = "";
			
			el.height = el.nHeight;
			return;
		} else
			el.opacityTimer = setTimeout(function(){
				show_element(el);
			}, 40);
			
		el.style.height = el.height + "px";
	}
	
	if(el.style.display != "")
		el.style.display = "";
}

function getPos( el )
{
	var pos = [0, 0];
	while( el != null ) {
		pos[1] += el.offsetTop;
		pos[0] += el.offsetLeft;
		el = el.offsetParent;
	}
	return pos;
}

function bind_tooltip(tooltip, element){
	var pos = getPos(element);
	element.addEventListener("mouseover", function(){
		tooltip.style.display = "block";
		tooltip.style.left = pos[0];
		tooltip.style.top = pos[1];
	});
	element.addEventListener("mouseout", function(){
		tooltip.style.display = "none";
	});
}

function setup_page(){
	var tooltips = document.getElementsByTagName("tooltip");
	for(var i=0; i<tooltips.length; i++) {
		bind_tooltip(tooltips[i], document.getElementById(tooltips[i].getAttribute("for")));
		
	}
	
	var advanced_boxes = [];
	var show_advanced = false;
	var toggle_button = document.getElementById("toggle_options");
	var advanced = [document.getElementById("advanced_left"),
					document.getElementById("advanced_right")];
	toggle_button.addEventListener("click", function(){
	
		show_advanced = !show_advanced;
		toggle_button.setAttribute("value", (show_advanced ? "▲ Hide Advanced ▲" : "▼ Show Advanced ▼"));
		
		if(show_advanced) {
			show_element(advanced[0]);
			show_element(advanced[1]);
		} else {
			hide_element(advanced[0]);
			hide_element(advanced[1]);
		}
			
	}, true);
}

setup_page();
</script></body></html>
