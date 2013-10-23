<?php
$widget = VirtualPages::fetchWidget($_GET['id']);
?><center><h2>Are you sure you want to delete the '<?php echo $widget['type']; ?>' Widget?</h2>
<input onclick="ControlPanel.loadPage('Pages', 'Manage', {delwidget: <?php echo $_GET['id']; ?>})" type="button" class="button" value="Delete It"> <input type="button" class="button" onclick="ControlPanel.closePopup()" value="Nevermind"></center>
