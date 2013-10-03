<?php
function startswith($string, $test)
{
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, 0, $testlen) === 0;
}
?>
