<?php

namespace App\Helper;

class ArrayHelper
{
    /**
     * Get value from array by key, return null if not found.
     */
    public static function getValueFromArray(string $key, array $array)
    {
        return $array[$key] ?? null;
    }
}
