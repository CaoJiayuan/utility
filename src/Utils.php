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

    protected static $onceValues = [];
    protected static $onceValueCreates = [];
    protected static $onceValueTTls = [];
    protected static $onceValueExpires = [];

    /**
     * Get a value, cached in request lifetime
     * @param $key
     * @param \Closure $processor
     * @param null $ttl
     * @return mixed
     */
    public static function once($key, \Closure $processor, $ttl = null)
    {
        if (in_array($key, static::$onceValueExpires)) {
            if (array_key_exists($key, static::$onceValues)) {
                unset(static::$onceValues[$key]);
            }
            static::$onceValueExpires = array_filter(static::$onceValueExpires, function ($v) use ($key) {
                return $v != $key;
            });
        }

        if (array_key_exists($key, self::$onceValueCreates) && array_key_exists($key, self::$onceValueTTls)) {
            $time = self::$onceValueCreates[$key];
            $valueTTl = self::$onceValueTTls[$key];
            if ($time + $valueTTl <= time()) {
                if (array_key_exists($key, static::$onceValues)) {
                    unset(static::$onceValues[$key]);
                }
            }
        }

        if (array_key_exists($key, static::$onceValues)) {
            return static::$onceValues[$key];
        }

        if ($ttl) {
            self::$onceValueCreates[$key] = time();
            self::$onceValueTTls[$key] = $ttl;
        }

        return static::$onceValues[$key] = $processor();
    }

    public static function expireOnceValue($key)
    {
        return static::$onceValueExpires[] = $key;
    }
}
