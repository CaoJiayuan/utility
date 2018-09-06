<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/29
 * Time: 上午11:35
 */

namespace CaoJiayuan\Utility\Promise;


use CaoJiayuan\Utility\Exceptions\UnhandledPromiseException;
use function CaoJiayuan\Utility\use_as_callable;
use function CaoJiayuan\Utility\use_as_closure;

class Promise implements PromiseInterface, \JsonSerializable
{
    private $id;

    private $executor;

    protected $status;

    protected $then;

    protected $catch;

    protected $fulfilled = null;

    protected $rejected = null;
    /**
     * @var static
     */
    protected $next = null;
    /**
     * @var null
     */
    private $prev;


    /**
     * Promise constructor.
     * @param $executor
     * @param Promise $prev
     */
    public function __construct($executor, Promise $prev = null)
    {
        $this->id = $this->generateId();
        $this->status = static::STATUS_PENDING;
        $this->executor = use_as_callable($executor);
        $this->prev = $prev;
    }

    protected function generateId()
    {
        return md5(microtime(true) . uniqid());
    }

    public function then(callable $fulfilled = null, callable $rejected = null)
    {
        $this->fulfilled = $fulfilled;
        $this->rejected = $rejected;

        return $this->next();
    }

    protected function next()
    {
        $this->next = new static(null, $this);

        return $this->next;
    }

    public static function resolve($resolve)
    {
        return new static(function ($res) use ($resolve) {
           if (is_callable($resolve)) {
               $res(call_user_func($resolve));
           } else {
               $res($resolve);
           }
        });
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
        $this->prev && $this->prev->resolveIfNotResolved();

        $fulfilled = $this->fulfilled;
        $reject = $this->rejected;
        if ($this->status == static::STATUS_PENDING) {
            $this->status = static::STATUS_RESOLVING;
            $rejector = $this->getRejector();
            try {
                call_user_func_array($this->executor, [function ($val) use ($fulfilled) {
                    $return = use_as_closure($fulfilled)($val);

                    $this->next && $this->next->setExecutor(function ($res, $rej) use ($return, $fulfilled, $val) {
                        if ($return instanceof self) {
                            $return->then($res, $rej);
                            $return->resolveIfNotResolved();
                        } else {
                            $res(is_null($fulfilled) ? $val : $return);
                        }
                    })->resolveIfNotResolved();

                }, $rejector]);
                $this->status = static::STATUS_FULFILLED;
            } catch (\Exception $exception) {
                if (is_null($reject) && is_null($this->next)) {
                    $this->status = static::STATUS_REJECTED;
                    throw $exception;
                }
                $this->status = static::STATUS_FULFILLED;
                $reason = $exception instanceof UnhandledPromiseException ? $exception->getReason() : $exception;

                if (is_null($this->next)) {
                    call_user_func($reject, $reason);
                } else {
                    $reject = $reject ?: $this->getRejector();

                    $this->next->setExecutor(function ($res, $rej) use ($reject, $reason) {
                        $result = call_user_func($reject, $reason);

                        if ($result instanceof self) {
                            $result->then($res, $rej);
                            $result->resolveIfNotResolved();
                        } else {
                            $res($result);
                        }

                    })->resolveIfNotResolved();
                }
            }
        }

        return $this->status;
    }

    public function rejected(callable $cb)
    {
        return $this->then(null, $cb);
    }


    function __destruct()
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
                $append = '';
                if ($reason instanceof \Throwable) {
                    $msg = $reason->getMessage();
                    $append = ", message [$msg]";
                }
                throw new UnhandledPromiseException($reason, "Unhandled exception [$exClass]" . $append, 500, $reason instanceof \Throwable ? $reason : null);
            } else {
                throw new UnhandledPromiseException($reason, "Unhandled promise exception message [$reason]", 500);
            }
        };
    }

    /**
     * @param \Closure $executor
     * @return $this
     */
    public function setExecutor(\Closure $executor)
    {
        $this->executor = $executor;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id'     => $this->id,
            'status' => $this->status,
            'next'   => $this->next
        ];
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}
