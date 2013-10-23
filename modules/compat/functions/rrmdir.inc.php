<?php
function rrmdir($path) {
	return is_file($path) ? @unlink($path) : array_map('rrmdir', glob($path.'/*')) == @rmdir($path);
}
?>
