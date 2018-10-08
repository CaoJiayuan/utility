<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/10/7
 * Time: 下午4:18
 */

namespace CaoJiayuan\Utility\Coroutine;


use SplQueue;

class Hub
{
    protected $queue;

    protected static $instance;

    public function __construct()
    {
        $this->queue = new SplQueue();
    }

    public function enqueue(Task $task)
    {
        $this->queue->enqueue($task);
    }

    public function run()
    {
        while (!$this->queue->isEmpty()) {
            $task = $this->queue->dequeue();
            $task->run();

            if (!$task->finished()) {
                $this->queue->enqueue($task);
            }
        }
    }

    /**
     * @return static
     */
    public static function singleton()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public static function coroutine($runner)
    {
        $instance = self::singleton();
        $run = $runner;
        if (is_callable($runner)) {
            $run = (function () use ($runner) {
                $data = $runner();
                if ($data instanceof \Iterator) {
                    foreach($data as $key => $item) {
                        yield $key => $item;
                    }
                } else {
                    yield;
                }
            })();
        }

        $instance->enqueue(new Task($run));

        return $instance;
    }

    public static function runAll()
    {
        $instance = self::singleton();

        $instance->run();

        return $instance;
    }
}
