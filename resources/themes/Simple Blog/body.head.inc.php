<ol class="navigation"><?
$menu = Triggers::broadcast("Template", "GenerateMenuLayout");
if(!$menu || !count($menu))
	$menu = Array("Home" => "/", "About Framework" => "/about:framework");
	
$size = 800/count(array_keys($menu));
foreach($menu as $text => $entry) {
	if(is_numeric($text)) {
		$text = $entry;
		$entry = StringFormat::idForDisplay($text);
	}
	
	$entry = BASE_URI . relativepath($entry);
	echo "<li><a style='width: " . $size . "px' href='$entry'>$text</a></li>";
}
?></ol>
