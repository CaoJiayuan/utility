<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/11/20
 * Time: 下午4:05
 */

namespace CaoJiayuan\Utility;


class Utils
{

    protected static $singleValues = [];
    protected static $singleValueExpires = [];

    /**
     * Get a value, cached in request lifetime
     * @param $key
     * @param \Closure $processor
     * @return mixed
     */
    public static function acquireSingleValue($key, \Closure $processor)
    {
        if (in_array($key, static::$singleValueExpires)) {
            if (array_key_exists($key, static::$singleValues)) {
                unset(static::$singleValues[$key]);
            }
            static::$singleValueExpires = array_filter(static::$singleValueExpires, function ($v) use ($key) {
                return $v != $key;
            });
        }

        if (array_key_exists($key, static::$singleValues)) {
            return static::$singleValues[$key];
        }

        return static::$singleValues[$key] = $processor();
    }

    public static function expireSingleValue($key)
    {
        return static::$singleValueExpires[] = $key;
    }
}
