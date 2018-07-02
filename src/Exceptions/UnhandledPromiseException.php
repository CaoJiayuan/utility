<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/7/2
 * Time: 上午11:14
 */

namespace CaoJiayuan\Utility\Exceptions;


use Throwable;

class UnhandledPromiseException extends \LogicException
{

    private $reason;

    public function __construct($reason, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->reason = $reason;
        parent::__construct($message, $code, $previous);
    }


    public function getReason()
    {
        return $this->reason;
    }
}
