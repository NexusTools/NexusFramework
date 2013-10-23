<?php

$database = UserGroups::getDatabase();
$table = "members";

if (isset($_GET['rem'])) {
	$userdisplay = User::getDisplayNameByID($_GET['rem']);
	if (isset($_GET['del'])) {
		if (UserGroups::getDatabase()->delete("members", Array("group" => $_GET['id'], "user" => $_GET['rem'])))
			echo "User `$userdisplay` Removed";
		else
			echo "An internal error occured.";
		return;
	}
	$groupname = User::getGroupNameForID($_GET['id']);
	echo "<center><h2>Remove user `$userdisplay` from group `$groupname`?</h2>";
	echo "<input type=\"button\" class=\"button\" onclick=\"ControlPanel.loadPage('Users', 'Group Members', {id: ";
	echo $_GET['id'];
	echo ", del: true, rem: ";
	echo $_GET['rem'];
	echo "});\" value=\"Remove\" /> ";
	echo "<input type=\"button\" class=\"button\" onclick=\"ControlPanel.closePopup()\" value=\"Nevermind\" /></center>";
	return Array(false, Array("title" => "Groups", "action" => "ControlPanel.loadPage('Users', 'Groups');"), Array("title" => $groupname, "action" => "ControlPanel.loadPage('Users', 'Group Members', {id: ".$_GET['id']."});"), "Remove Member");
}

echo "<pagebuttons>";
ControlPanel::renderStockButton("new", "ControlPanel.loadPopup('Users', 'Add Group Member', {group: ".$_GET['id']."});", "Add Member");
echo "</pagebuttons>";

echo "<table><tr><th>User</th><th style=\"width: 40%\">Actions</th></tr>";

$rows = $database->select($table, Array("group" => $_GET['id']));

$alt = false;
if (count($rows))
	foreach ($rows as $row) {
		echo "<tr class=\"link";
		if ($alt)
			echo " alt";
		$alt = !$alt;
		echo "\" action=\"Users/Group Members?id=";
		echo $_GET['id'];
		echo "&rem=";
		echo $row['user'];
		echo "\"><td>";
		echo User::getDisplayNameByID($row['user']);
		echo "</td><td><a title=\"Remove User from Group\" href=\"control://Users/Group Members?id=";
		echo $_GET['id'];
		echo "&rem=";
		echo $row['user'];
		echo "&popup=true\">Remove</a></td></tr>";
	}
else {
	echo "<tr><td colspan=\"2\">No Users in Group</td></tr>";
}
echo "</table>";

return Array(false, Array("title" => "Groups", "action" => "ControlPanel.loadPage('Users', 'Groups');"), User::getGroupNameForID($_GET['id']));
?>
