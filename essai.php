<?php

/*
 * Vesta
 */

$cfg = [
    '1d4',
    ' 2d6   ',
    ' 2d6       +   1',
    '1d6+ 2d12 +1'
];

foreach ($cfg as $roll) {
    $dice = preg_split('#\s*\+\s*#', trim($roll));
    echo "\nNEW\n";
    foreach ($dice as $oneDie) {
        $dump = [];
        preg_match('#^(?:([\d])d([\d]+))|(?:([\d]+))$#', $oneDie, $dump, PREG_UNMATCHED_AS_NULL);
        var_dump($dump[1], 'd', $dump[2], $dump[3], '+');
    }
}