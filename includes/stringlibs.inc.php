<?php

function endswith($string, $test){
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, -$testlen) === 0;
}

function startswith($string, $test)
{
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, 0, $testlen) === 0;
}

function pluralize($number, $suffix){
    if($number > 1)
        $suffix .= "s";
    return number_format($number) . "$suffix";
}

function format_bytes($size, $shortunits=false) {
    if($shortunits)
        $units = array('B', 'K', 'M', 'G', 'T');
    else
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
}

?>
