<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/29
 * Time: 上午9:51
 */

namespace CaoJiayuan\Utility\Http;


use CaoJiayuan\Utility\Ob\ObjectOb;
use CaoJiayuan\Utility\Ob\Value;
use GuzzleHttp\Client as Guzzle;

/**
 * Class Client
 * @package CaoJiayuan\Utility\Http
 * @mixin Guzzle
 */
class Client extends ObjectOb
{
    /**
     * @var array
     */
    private $options;
    private $guzzle = null;

    public function __construct($options = [])
    {
        $this->options = $options;
        parent::__construct(new Value(new Guzzle()));
    }

    public function config($options = [])
    {
        $this->options = $options;

        return $this;
    }

    public function baseUri($uri)
    {
        $this->options['base_uri'] = $uri ?: '';

        return $this;
    }

    public function header($key, $value = null)
    {
        if (!isset($this->options['headers'])) {
            $this->options['headers'] = [];
        }
        $headers = [];
        if (is_array($key)) {
            $headers = $key;
        } elseif (is_string($key)) {
            if (!is_null($value)) {
                $headers = [
                    $key => $value
                ];
            } else {
                if (isset($this->options['headers'][$key])) {
                    unset($this->options['headers'][$key]);
                }
            }
        }
        foreach ($headers as $k => $v) {
            $this->options['headers'][$k] = $v;
        }

        return $this;
    }

    public function userAgent($ua)
    {
        return $this->header('User-Agent', $ua);
    }

    public function accept($accept)
    {
        return $this->header('Accept', $accept);
    }

    public function acceptJson()
    {
        return $this->accept('application/json');
    }

    public function batch($urls, $method, $options = [])
    {
        $requests = function () use ($urls, $method, $options) {
            foreach ($urls as $url) {
                yield function () use ($url, $method, $options) {
                    /** @var Guzzle $val */
                    $val = $this->_value_()->getVal();
                    return $val->requestAsync($method, $url, $options);
                };
            }
        };

        $requester = new BatchRequester($this->_value_()->getVal(), $requests());

        return $requester;
    }


    protected function _watch_($now, $old)
    {
        // TODO: Implement _watch_() method.
    }

    /**
     * @param Guzzle $value
     * @param $param
     * @return Guzzle
     */
    protected function _reading_($value, $param)
    {
        if (is_null($this->guzzle)) {
            $this->guzzle = new Guzzle($this->options);
            $this->_value_()->setVal($this->guzzle);
        }

        return $value;
    }
}
