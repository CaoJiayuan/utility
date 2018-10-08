<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/10/7
 * Time: 下午4:18
 */

namespace CaoJiayuan\Utility\Coroutine;


use SplQueue;

class Scheduler
{
    protected $queue;

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
}
