<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/29
 * Time: 上午11:35
 */

namespace CaoJiayuan\Utility\Promise;


use CaoJiayuan\Utility\Exceptions\UnhandledPromiseException;

class Promise implements PromiseInterface
{

    private $executor;

    protected $status;

    protected $then;

    protected $catch;

    protected $fulfilled = null;
    protected $rejected = null;
    protected $next = null;


    /**
     * Promise constructor.
     * @param $executor
     */
    public function __construct($executor)
    {
        $this->status = static::STATUS_PENDING;
        $this->executor = use_as_callable($executor);
    }

    public function then(callable $fulfilled = null, callable $rejected = null)
    {
        $this->fulfilled = $fulfilled;
        $this->rejected = $rejected;
        return $this;
    }

    protected function next(callable $fulfilled = null, callable $rejected = null)
    {
        $this->next = new static(function ($resolve, $reject) use ($fulfilled) {


            call_user_func_array($this->executor, [use_as_callable($fulfilled), $reject]);

        });
        $this->next->rejected($rejected);

        return $this->next;
    }

    public static function resolve($resolve)
    {
        return new static($resolve);
    }

    public static function reject($reason)
    {
        $promise = new static(function ($resolve, $rejected) use ($reason) {
            $rejected($reason);
        });

        return $promise;
    }

    public function resolveIfNotResolved()
    {
        $fulfilled = $this->fulfilled;
        $reject = $this->rejected;
        if ($this->status == static::STATUS_PENDING) {
            $rejector = $this->getRejector();
            try {
                $value = call_user_func_array($this->executor, [use_as_callable($fulfilled), $rejector]);
                $this->status = static::STATUS_FULFILLED;
                return static::resolve($value);
            } catch (UnhandledPromiseException $exception) {
                if (is_null($reject)) {
                    $this->status = static::STATUS_REJECTED;
                    throw $exception;
                }
                $this->status = static::STATUS_FULFILLED;
                return new static(function ($res, $rej) use ($reject, $exception) {
                    $val = call_user_func($reject, $exception->getReason());

                    $res($val);
                });
            }
        }

        return $this;
    }

    public function rejected(callable $cb)
    {
        return $this->then(null, $cb);
    }


    public function __destruct()
    {
        $this->resolveIfNotResolved();
    }

    /**
     * @return \Closure
     */
    protected function getRejector(): \Closure
    {
        return function ($reason) {
            if (is_object($reason)) {
                $exClass = get_class($reason);
                throw new UnhandledPromiseException($reason, "Unhandled exception [$exClass]", 500, $reason instanceof \Throwable ? $reason : null);
            } else {
                throw new UnhandledPromiseException($reason, "Unhandled promise exception message [$reason]", 500);
            }
        };
    }
}
