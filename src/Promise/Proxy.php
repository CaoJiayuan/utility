<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/7/3
 * Time: 下午4:06
 */

namespace CaoJiayuan\Utility\Promise;


class Proxy
{
    /**
     * @var Promise[]
     */
    protected static $promises = [];

    public static function promise($executor)
    {
        $promise = new Promise($executor);

        static::$promises[] = $promise;

        return $promise;
    }

    public static function getPromises()
    {
        return self::$promises;
    }

    public static function resolveAll()
    {
        foreach(self::getPromises() as $promise) {
            $promise->resolveIfNotResolved();
        }
    }
}
