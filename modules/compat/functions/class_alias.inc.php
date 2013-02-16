<?
function class_alias($original, $alias) {
    eval('abstract class ' . $alias . ' extends ' . $original . ' {}');
}
?>
