<?php
function __userCounter__countMenu() {
	UserCounter::tick();
	$entries = Array();
	$staff = UserCounter::getOnlineStaff();
	if (count($staff)) {
		foreach ($staff as $user) {
			$page = UserCounter::getUserPage($user);
			try {
				$avatar = User::getAvatarByID($user);
				if ($avatar)
					$avatar = "<img src='$avatar' />";
				else
					$avatar = "";
			} catch (Exception $e) {
				$avatar = "";
			}
			array_push($entries, $avatar.User::getFullNameByID($user)."<span>$page</span>");
		}

		array_push($entries, "----");
	}
	array_push($entries, UserCounter::getMemberCount()." Members");
	array_push($entries, UserCounter::getGuestCount()." Guests");
	return $entries;
}

function __userCounter__importScript($module, $event, $arguments) {
	switch ($event) {
	case "Header":
		Template::addScript(dirname(__FILE__).DIRSEP."live-update.js");
		break;
	}
}

Triggers::watchModule("ControlPanel", "__userCounter__importScript");
ControlPanel::registerToolbarWidget("{{UserCounter::getOnlineCount()}} User(s) Online",
	"__userCounter__countMenu", "Users/Online", "online-users");
?>
