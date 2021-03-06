<widget><h1>Interpolation Reference</h1>
<p>Each HTML widget has the ability to access dynamic framework apis and constants.<br />
To use this functionality simply wrap your commands in {{ }} braces, the framework will parse and output the result of the command.<br />
Exceptions thrown in these blocks are caught and output in JSON format without interupting generation of the website.</p>
<h2>Syntax</h2>
<p>The syntax to use methods in interpolation is identical to how you would write them with PHP,<br />
Except that you don't need to include quotes unless the argument contains a comma.</p>
<pre>{{Class::method(arguments separated by commas)}}</pre>
<h2>Select a class below to view more information about it.</h2></widget>
<?php
foreach (ClassLoader::getRegisteredClasses() as $class) {
	$infoPath = substr($class, 0, strlen($class) - 4).".json";
	if (!is_file($infoPath))
		continue;

	$classInfo = json_decode(file_get_contents($infoPath), true);
	if (!$classInfo || !array_key_exists("name", $classInfo))
		continue;

	if (!array_key_exists("description", $classInfo))
		$classInfo['description'] = "Description missing...";

	$classID = Framework::uniqueHash($class, Framework::URLSafeHash);
	echo "<a href=\"";
	echo BASE_URI;
	echo "about:interpolation/$classID\"><widget><h3 style='text-transform: none !important;'>$classInfo[name]<br /><small>";
	echo nl2br($classInfo['description']);
	echo "</small></h3></widget></a>";
}
?>
