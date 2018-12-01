<?php
function d()
{
    $args = func_get_args();
    echo '<pre>';
    foreach ($args as $arg) {
        var_dump($arg);
    }
    echo '</pre>';
}

function dd()
{
    d(func_get_args());
    die();
}

function onlyAsciiChars($str)
{
    foreach (str_split($str) as $i => $l) {
        $ord = ord($l);
        if ($ord < 32 || $ord > 127) {
            $str[$i] = ' ';
        }
    }

    return $str;
}
