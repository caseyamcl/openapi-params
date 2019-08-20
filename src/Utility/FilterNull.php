<?php

declare(strict_types=1);

namespace Paramee\Utility;

class FilterNull
{
    /**
     * Filter NULL values out of an array (with strict type-checking)
     * @param array $arr
     * @return array
     */
    public static function filterNull(array $arr): array
    {
        return array_filter($arr, function ($value) {
            return $value !== null;
        });
    }
}