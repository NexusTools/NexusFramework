<h1>Interpolation Reference</h1>
<p>Each HTML widget has the ability to access dynamic framework apis and constants.<br />
To use this functionality simply wrap your commands in {{ }}, the framework will parse and output the result of the command.<br />
Exceptions thrown in these blocks are caught and output in JSON format without interupting generation of the website.</p>
<h2>Registered APIs</h2>
<p>Select a class below to view more information about it.</p>
<?php
foreach (ClassLoader::getRegisteredClasses() as $class) {
	echo "<h2>$class</h2>";
	echo "<p>This class does not yet have a description.</p>";
}
?>
