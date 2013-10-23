<pagebuttons><?php
ControlPanel::renderStockButton("apply");
ControlPanel::renderStockButton("discard");
?></pagebuttons>
By Default<br />
<select><option>Blacklist Pages in Navigation</option>
<option>Blacklist All Pages</option>
<option>Whitelist All Pages</option></select>
<?php return Array(false, "ControlPanel Settings"); ?>
