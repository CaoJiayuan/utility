<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/29
 * Time: 上午11:44
 */

namespace CaoJiayuan\Utility\Promise;


interface PromiseInterface
{
    const STATUS_PENDING = 'pending';

    const STATUS_RESOLVING = 'resolving';

    const STATUS_FULFILLED = 'fulfilled';

    const STATUS_REJECTED = 'rejected';

    public function then(callable $cb = null, callable $rejected = null);

    public function rejected(callable $cb);
}
