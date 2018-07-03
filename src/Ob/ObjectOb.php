<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/28
 * Time: 下午2:35
 */

namespace CaoJiayuan\Utility\Ob;



use CaoJiayuan\Utility\Exceptions\BadPropertyAccessException;

abstract class ObjectOb extends Ob
{
    protected $chainCall = false;

    public function __call($name, $arguments)
    {
        $obj = $this->_value_()->getVal([$name, false]);

        try {
            $result = call_user_func_array([$obj, $name], $arguments);
        } catch (\Exception $exception) {
            $method = get_class($obj) . ':' .$name;
            $message = $exception->getMessage();
            throw new \BadMethodCallException("Method call exception, method [$method], message [$message]", 0, $exception);
        }

        if ($this->chainCall) {
            return $this;
        }

        return $result;
    }

    public function __get($name)
    {
        $obj = $this->_value_()->getVal([$name, false]);

        try {
            return $obj->$name;
        } catch (\Exception $exception) {
            $prop = get_class($obj) . ':' .$name;
            $message = $exception->getMessage();
            throw new BadPropertyAccessException("Property access exception, property [$prop], message [$message]", 0, $exception);
        }
    }
}
