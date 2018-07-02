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

if (!function_exists('use_as_closure')) {
    function use_as_closure($value) {
        if (is_callable($value)) {
            return function () use ($value) {
                return call_user_func_array($value, func_get_args());
            };
        }

        return function () use ($value) {
            return $value;
        };
    }
}
