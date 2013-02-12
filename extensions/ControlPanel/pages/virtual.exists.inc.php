<?php
return PageModule::countArguments() == 3 && ControlPanel::has(PageModule::getArgument(1), PageModule::getArgument(2));
?>
