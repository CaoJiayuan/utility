<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/29
 * Time: 上午10:42
 */

namespace CaoJiayuan\Utility\Http;


use GuzzleHttp\Client;
use GuzzleHttp\Pool;

class BatchRequester
{


    private $requests;
    /**
     * @var Client
     */
    private $client;
    private $fulfilled;
    private $rejected;

    /**
     * BatchRequester constructor.
     * @param Client $client
     * @param array|iterable $requests
     */
    public function __construct(Client $client, $requests)
    {
        $this->requests = $requests;
        $this->client = $client;
    }

    public function then(callable $cb)
    {
        $this->fulfilled = $cb;
        return $this;
    }

    public function rejected(callable $cb)
    {
        $this->rejected = $cb;
        return $this;
    }

    public function resolve()
    {
        $pool = new Pool($this->client, $this->requests, [
            'concurrency' => 5,
            'fulfilled'   => $this->fulfilled,
            'rejected'    => $this->rejected,
        ]);

        return $pool->promise()->wait();
    }

    function __destruct()
    {
        $this->resolve();
    }
}
