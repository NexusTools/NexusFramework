<widget><?php
$classInfo = PageModule::getValue("ClassInfo");
?><h1 style='text-transform: none !important;'><?php echo $classInfo['name']; ?></h1>
<p><?php echo nl2br(htmlentities($classInfo['long-description'])); ?></p>
<?php
if(array_key_exists("virtual-modifiers", $classInfo)) {
    echo "<h2>Virtual Modifiers</h2><p>";
    if(array_key_exists("virtual-modifiers-info", $classInfo))
        echo nl2br(htmlentities($classInfo['virtual-modifiers-info']));
    else
        echo "Virtual modifiers are strings that can be put on the end of any method to change how it works.<br />They're meant to be used one at a time.";
    echo "</p><table><tr><th>Name</th><th>Description</th></tr>";
    foreach($classInfo["virtual-modifiers"] as $name => $description) {
        echo "<tr><td>$name</td><td>";
        echo nl2br(htmlentities($description));
        echo "</td></tr>";
    }
    echo "</table>";
}
?>
<?php
if(array_key_exists("methods", $classInfo)) {
    echo "<h2>Methods</h2>";
    echo "<table><tr><th>Name</th><th>Arguments</th><th>Description</th></tr>";
    foreach($classInfo["methods"] as $name => $method) {
        echo "<tr><td>$name</td><td>$method[arguments]</td><td>";
        echo nl2br(htmlentities($method['description']));
        echo "</td></tr>";
    }
    echo "</table>";
}
?></widget>
