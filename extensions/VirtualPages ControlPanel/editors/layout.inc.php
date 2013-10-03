<?php
switch($mode){
	case EditCore::RENDER:
if(array_key_exists("allow-inherit", $meta)) {
?><input id="__cp_<? echo $name; ?>_layout_inherit" style="position: relative; top: -35px;" name="<? echo $name; ?>" value="-1" type="radio"<?
if($value===false || $value == -1)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_<? echo $name; ?>_layout_inherit">
<? VirtualPages::renderLayoutVisual(-1, 2); ?>
</label>

<input id="__cp_<? echo $name; ?>_layout0" style="position: relative; top: -35px;" name="<? echo $name; ?>" value="0" type="radio"<?
if($value == 0 && $value !== false)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_<? echo $name; ?>_layout0">
<? VirtualPages::renderLayoutVisual(0, 2); ?>
</label>
<? } else { ?>
	<input id="__cp_<? echo $name; ?>_layout0" style="position: relative; top: -35px;" name="<? echo $name; ?>" value="0" type="radio"<?
if(!$value)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_<? echo $name; ?>_layout0">
<? VirtualPages::renderLayoutVisual(0, 2); ?>
</label>
<? } ?>

<input id="__cp_<? echo $name; ?>_layout1" style="position: relative; top: -35px;" name="<? echo $name; ?>" value="1" type="radio"<?
if($value == 1)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_<? echo $name; ?>_layout1">
<? VirtualPages::renderLayoutVisual(1, 2); ?>
</label>

<input id="__cp_<? echo $name; ?>_layout2" style="position: relative; top: -35px;" name="<? echo $name; ?>" value="2" type="radio"<?
if($value == 2)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_<? echo $name; ?>_layout2">
<? VirtualPages::renderLayoutVisual(2, 2); ?>
</label>

<input id="__cp_<? echo $name; ?>_layout3" style="position: relative; top: -35px;" name="<? echo $name; ?>" value="3" type="radio"<?
if($value == 3)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_<? echo $name; ?>_layout3">
<?
VirtualPages::renderLayoutVisual(3, 2);
?></label><?
	break;
	
	case EditCore::VALIDATE:
		return true;
}
?>
