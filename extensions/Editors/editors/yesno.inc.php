<?php
switch ($mode) {
case EditCore::RENDER:
?><widget class="component switch" style="line-height: 26px; margin-bottom: 6px">
<input type="radio" value="1" name="<?php echo $name; ?>" id="__cp_<?php echo $name; ?>_inNavbarYes"<?php
if($value || $value == "Yes")
	echo " checked";
?> /><label for="__cp_<?php echo $name; ?>_inNavbarYes">Yes</label> 
<input type="radio" value="0" name="<?php echo $name; ?>" id="__cp_<?php echo $name; ?>_inNavbarNo"<?php
	if(!$value)
		echo " checked";
?> /><label for="__cp_<?php echo $name; ?>_inNavbarNo">No</label></widget><?php

case EditCore::VALIDATE:
	return true;
}
