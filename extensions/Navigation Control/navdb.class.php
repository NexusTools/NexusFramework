<?php
class NavigationDB extends EmuDatabase {
	public function getEntries() {
		return Navigation::getNavigationArray();
	}
}
?>
