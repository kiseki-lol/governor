<?php

function isJson($string) : bool {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

// god forgive me
function reverseOverflow(int $overflowedValue) : int {
    // 2^32
    $maxUint32 = 4294967296;
    $originalValue = $overflowedValue;

    if ($overflowedValue > 0 && $overflowedValue < $maxUint32 / 2) {
        $originalValue = $overflowedValue + 2 * $maxUint32;
    } 
    else if ($overflowedValue < 0) {
        $originalValue = $overflowedValue + 2 * $maxUint32;
    }

    return $originalValue;
}