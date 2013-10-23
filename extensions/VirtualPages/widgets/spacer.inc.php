<?php
switch (VirtualPages::getMode()) {
case VirtualPages::UPDATE_CONFIG:
case VirtualPages::CREATE:
	return Array("image" => "", "height" => 20, "image-position" => 1);

case VirtualPages::RENDER:
	echo "<div style=\"height: 20px;\"></div>";
	return;

}
?>
