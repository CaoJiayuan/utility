<?php namespace CaoJiayuan\Utility\Validation;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @author caojiayuan
 */
class ValidationException extends HttpException
{
  const TYPE_REQUIRED = 'required';
  const TYPE_TYPE     = 'type';

  protected $type;

  protected $key;

  protected $data;

  protected $expectType;

  public function __construct($key, $type, $data, $expectType = null,string $message = null, int $statusCode = 422)
  {
    $this->key = $key;
    $this->type = $type;
    $this->data = $data;
    $this->expectType = $expectType;
    parent::__construct($statusCode, $this->parseMessage($type, $message));
  }

  protected function parseMessage($type, $message = null)
  {
    if (!is_null($message)) {
      return $message;
    }
    $method = "parse{$type}Message";

    if (method_exists($this, $method)) {
      return $this->{$method}();
    }

    return sprintf("validation error, type [%s], key [%s], data: %s", $this->type, $this->key, json_encode($this->data));
  }

  protected function parseRequiredMessage()
  {
    $data = $this->stringData();

    return sprintf("validation error, key [%s] is required, got data: %s", $this->key, $data);
  }


  protected function parseTypeMessage()
  {
    $data = $this->stringData();

    return sprintf("validation error, key [%s] expect type [%s], got data: %s", $this->key, $this->expectType, $data);
  }

  /**
   * @return mixed
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * @return mixed
   */
  public function getKey()
  {
    return $this->key;
  }

  /**
   * @return mixed
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * @return false|string
   */
  protected function stringData()
  {
    $data = is_string($this->data) ? $this->data : json_encode($this->data, JSON_UNESCAPED_UNICODE);

    return $data;
  }
}