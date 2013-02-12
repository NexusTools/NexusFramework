<?php
$page = VirtualPages::fetchPage($_GET['id']);
?><center><h2>Are you sure you want to delete '<? echo $page['title']; ?>'?</h2>
<div style="margin-top: -8px; margin-bottom: 8px;"><? echo cleanpath("/" . $page['path']); ?></div>
<input onclick="ControlPanel.loadPage('Pages', 'Manage', {delete: <? echo $_GET['id']; ?>})" type="button" class="button" value="Delete It"> <input type="button" class="button" onclick="ControlPanel.closePopup()" value="Nevermind"></center>
