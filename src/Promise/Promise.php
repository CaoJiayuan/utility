<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/29
 * Time: 上午11:35
 */

namespace CaoJiayuan\Utility\Promise;


class Promise implements PromiseInterface
{

    private $executor;

    protected $status;

    protected $then;

    protected $catch;
    /**
     * @var array
     */
    private $params;

    /**
     * Promise constructor.
     * @param $executor
     * @param array $params
     */
    public function __construct($executor, $params = [])
    {
        $this->executor = use_as_callable($executor);
        $this->params = $params;
    }

    public function then(callable $cb)
    {
        return new static(function ($resolve, $reject) use ($cb) {
            $result = $this->resolveIfNotResolved($reject);

            call_user_func($cb, $result);
            return $resolve();
        });
    }

    public static function resolve($resolve)
    {

    }

    public function resolveIfNotResolved(callable $reject)
    {
        try {
            $result = call_user_func_array($this->executor, $this->params);
        } catch (\Exception $exception) {
            return call_user_func_array($reject, [$exception]);
        }

        return $result;
    }

    public function rejected(callable $cb)
    {
        // TODO: Implement rejected() method.
    }
}
