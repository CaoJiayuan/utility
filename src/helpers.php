<?php


if (!function_exists('use_as_callable')) {
    function use_as_callable($value)
    {
        if (is_callable($value)) {
            return $value;
        }

        return function () use ($value) {
            return $value;
        };
    }
}
