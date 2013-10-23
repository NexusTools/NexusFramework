<?php
switch ($mode) {
case EditCore::RENDER:
	if (array_key_exists("allow-inherit", $meta)) {
?><input id="__cp_<?php echo $name; ?>_layout_inherit" style="position: relative; top: -35px;" name="<?php echo $name; ?>" value="-1" type="radio"<?php
if($value===false || $value == -1)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_<?php echo $name; ?>_layout_inherit">
<?php VirtualPages::renderLayoutVisual(-1, 2); ?>
</label>

<input id="__cp_<?php echo $name; ?>_layout0" style="position: relative; top: -35px;" name="<?php echo $name; ?>" value="0" type="radio"<?php
if($value == 0 && $value !== false)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_<?php echo $name; ?>_layout0">
<?php VirtualPages::renderLayoutVisual(0, 2); ?>
</label>
<?php } else { ?>
	<input id="__cp_<?php echo $name; ?>_layout0" style="position: relative; top: -35px;" name="<?php echo $name; ?>" value="0" type="radio"<?php
if(!$value)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_<?php echo $name; ?>_layout0">
<?php VirtualPages::renderLayoutVisual(0, 2); ?>
</label>
<?php } ?>

<input id="__cp_<?php echo $name; ?>_layout1" style="position: relative; top: -35px;" name="<?php echo $name; ?>" value="1" type="radio"<?php
if($value == 1)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_<?php echo $name; ?>_layout1">
<?php VirtualPages::renderLayoutVisual(1, 2); ?>
</label>

<input id="__cp_<?php echo $name; ?>_layout2" style="position: relative; top: -35px;" name="<?php echo $name; ?>" value="2" type="radio"<?php
if($value == 2)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_<?php echo $name; ?>_layout2">
<?php VirtualPages::renderLayoutVisual(2, 2); ?>
</label>

<input id="__cp_<?php echo $name; ?>_layout3" style="position: relative; top: -35px;" name="<?php echo $name; ?>" value="3" type="radio"<?php
if($value == 3)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_<?php echo $name; ?>_layout3">
<?php
	VirtualPages::renderLayoutVisual(3, 2);
?></label><?php
	break;

case EditCore::VALIDATE:
	return true;
}
?>
