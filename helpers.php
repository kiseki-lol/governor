<?php

function isJson($string) : bool {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

// god forgive me
function reverseOverflow(int $overflowedValue) : int {
    // 2^32
    $maxUint32 = 4294967296;

    // add 2^32 to reverse the overflow
    if ($overflowedValue < 0) {
        // multiple overflow
        $originalValue = $overflowedValue + 2 * $maxUint32;
    } else {
        // no correction
        $originalValue = $overflowedValue;
    }

    return $originalValue;
}