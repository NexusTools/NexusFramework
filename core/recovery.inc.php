<?php

if(defined("INAPI")) {
	function recovery_show_page($data) {
		echo json_encode(Array("error" => (is_object($data)) ?
				($data instanceof Exception ? $data->getMessage() : $data->toString()) : $data));
	}
} else {
	function recovery_show_page($data) {
		while(ob_get_level() > NATIVE_OB_LEVEL)
			ob_end_clean();
		@ob_start();

		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header("Content-Type: text/html");
?><!doctype html>
<html><head><title>NexusFramework Issue Resolver</title>
<style>
body {
	cursor: default;
	margin-top: 30px;
	background: #072951;
	font-family: arial, georgia;
	font-size: 90%;
}

.page {
	width: 600px;
	margin: 0 auto;
	padding: 15px;
	border: solid 2pt gray;
	border-radius: 15px;
	background-color: white;
	-webkit-box-shadow: 0px 0px 15px 0px rgba(50, 50, 50, 1);
	-moz-box-shadow: 0px 0px 15px 0px rgba(50, 50, 50, 1);
	box-shadow: 0px 0px 15px 0px rgba(50, 50, 50, 1);
}

h1,
h2,
h3 {
	margin: 0px;
}

h1 {
	font-size: 120%;
}

h2 {
	font-size: 110%;
}

h3 {
	text-align: left; margin-top: 10px;
}

h4 {
	margin: 0px;
}

p {
	margin: 4px;
	margin-top: 0px;
	text-align: left;
}

pre,
div.container {
	width: 600px;
	max-height: 500px;
	overflow: auto;
}

* {
    outline: none;
}

table {
	margin-top: 50px;
	margin-bottom: 50px;
}

table td {
	color: #555;
	font-size: 12px;
	height: 50%;
	margin: 0px;
	padding: 0px;
}

input.button {
	color: #888;
	font-weight: bold;
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

input.text {
	border: solid 1pt #aaa;
	border-top: solid 1pt #777;
	color: #555;
	padding: 4px;
	background-color: white;
}

input.text:focus {
	color: black;
	border: solid 1pt #333;
	-webkit-box-shadow: 0px 1px 0px 0px rgba(0, 0, 0, 0.4);
	-moz-box-shadow: 0px 1px 0px 0px rgba(0, 0, 0, 0.4);
	box-shadow: 0px 1px 0px 0px rgba(0, 0, 0, 0.4);
}
</style>
</head><body><div align="center" class="page"><?
if(false){ ?>

<h1>A unrecoverable internal error occured.</h1>
<h2>Login to review this issue.</h2>
<form method="POST" action="<? echo ROOT_URL; ?>">
	<center><table>
		<tr><td style="padding-bottom: 0px;">Username</td></tr>
		<tr><td><input type="text" class="text" name="user" /></td></tr>
		<tr><td style="padding-bottom: 0px;">Password</td></tr>
		<tr><td><input type="password" class="text" name="pass" /></td></tr>
		<tr><td align="right"><input class="button" type="submit" value="Login"></td></tr>
	</table></center>
</form>
<?
} else {
?>
<h1><?
$exception = $data['exception'];
if(array_key_exists("type", $exception) && $exception['type'] && !is_numeric($exception['type']))
	echo "Uncaught $exception[type] Occured";
else
	echo "Unrecoverable Error Occured";
?></h1>
<h2><? echo date("F j, Y, g:i a", $data['date']); ?></h2>

<h3>Error Message</h3><p>
<?

if(array_key_exists("message", $exception)) {
	echo $exception['message'];
	if(array_key_exists("details", $exception) && $exception['details']) {
		echo "<br /><pre style='text-align: left; margin-left: 8px'>";
		print_r($exception['details']);
		echo "</pre>";
	}
} else if(array_key_exists("details", $exception) && $exception['details']) {
	echo "<pre style='text-align: left; margin-left: 8px'>";
	print_r($exception['details']);
	echo "</pre>";
} else
	echo "No message was associated with this error";
?></p>
<?
if(array_key_exists("file", $exception) &&
	array_key_exists("line", $exception)) {
?>
<h3>File Location and Line</h3>
<p>Occured in file <? echo $exception['file']; ?> on line <? echo $exception['line']; ?></p>
<? } ?>

<?
if(array_key_exists("trace", $exception)) {
?>
<h3><a style="font-size: 70%; float: right" id="previous-link" href="javascript:togglePrevious();void(0);">Open</a>Previous Exceptions</h3>
<div style="display: none" class="container" id="previous" align="left"><?
$prevExc = $exception;
while(is_array($prevExc = $prevExc['previous'])) {
	echo "<p style=\"margin-top: 8px\"><b>";
	if(array_key_exists("type", $prevExc))
		echo "$prevExc[type]: ";
	else
		echo "Error: ";
	echo htmlentities($prevExc['message']);
	echo "</b>";
	if(array_key_exists("file", $exception) &&
		array_key_exists("line", $exception)) {
	?><br />
	Occured in file <? echo $exception['file']; ?> on line <? echo $exception['line']; ?>
	<? }
	echo "</p>";
}
?></div>
<script>

var prevExceptionOpen = false;
function togglePrevious(){
	var trace = document.getElementById("previous");
	var link = document.getElementById("previous-link");
	console.log(trace);
	prevExceptionOpen = !prevExceptionOpen;
	if(prevExceptionOpen){
		link.innerHTML = "Close";
		trace.style.display = "block";
	} else {
		link.innerHTML = "Open";
		trace.style.display = "none";
	}
}
</script>
<? } ?>


<?
if(array_key_exists("trace", $exception)) {
?>
<h3><a style="font-size: 70%; float: right" id="trace-link" href="javascript:toggleTrace();void(0);">Open</a>Stack Trace</h3>
<pre style="display: none" id="trace" align="left"><?
if(defined("JSON_PRETTY_PRINT"))
	echo htmlentities(json_encode($exception['trace'], JSON_PRETTY_PRINT));
else
	echo htmlentities(print_r($exception['trace'], true));
?></pre>

<script>

var traceOpen = false;
function toggleTrace(){
	var trace = document.getElementById("trace");
	var link = document.getElementById("trace-link");
	console.log(trace);
	traceOpen = !traceOpen;
	if(traceOpen){
		link.innerHTML = "Close";
		trace.style.display = "block";
	} else {
		link.innerHTML = "Open";
		trace.style.display = "none";
	}
}
</script>
<? } ?>
<br /><br /><h2>Known Resolutions</h2>
Sorry but this error doesn't match anything currently in the database.<br /><br />
<? } ?></div></body></html><? 
	}
} ?>
