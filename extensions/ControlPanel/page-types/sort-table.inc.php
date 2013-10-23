<?php
return "<?php\nreturn ControlPanel::renderSortPage($definition[database], \"$definition[table]\", ".to_php($definition['fields']).", ".(isset($definition['hasParenting']) ? "true" : "false").", ".to_php(isset($definition['where']) ? $definition['where'] : false).")\n?>";
?>
