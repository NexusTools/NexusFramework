<?php
$data = Array();
$data['staff'] = Array();
$data['members'] = UserCounter::getMemberCount();
$data['guests'] = UserCounter::getGuestCount();

UserCounter::update();
$staff = UserCounter::getOnlineStaff();
foreach ($staff as $user) {
	$page = UserCounter::getUserPage($user);
	try {
		$avatar = User::getAvatarByID($user);
	} catch (Exception $e) {
	}
	array_push($data['staff'], Array($avatar, User::getFullNameByID($user), $page));
}

return $data;
?>
