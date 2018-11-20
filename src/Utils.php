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

    /**
     * Get a value, cached in request lifetime
     * @param $key
     * @param \Closure $processor
     * @return mixed
     */
    public static function acquireSingleValue($key, \Closure $processor)
    {
        if (array_key_exists($key, static::$singleValues)) {
            return static::$singleValues[$key];
        }

        return static::$singleValues[$key] = $processor();
    }
}
