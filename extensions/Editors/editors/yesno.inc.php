<?php
switch($mode){
	case EditCore::RENDER:
		?><widget class="component" style="line-height: 26px; margin-bottom: 6px">
<input type="radio" value="1" name="<? echo $name; ?>" id="__cp_<? echo $name; ?>_inNavbarYes"<?
if($value || $value == "Yes")
	echo " checked";
?> /><label for="__cp_<? echo $name; ?>_inNavbarYes">Yes</label> 
<input type="radio" value="0" name="<? echo $name; ?>" id="__cp_<? echo $name; ?>_inNavbarNo"<?
	if(!$value)
		echo " checked";
?> /><label for="__cp_<? echo $name; ?>_inNavbarNo">No</label></widget><?
	
	case EditCore::VALIDATE:
		return true;
}
