<?php namespace CaoJiayuan\Utility\Validation;


/**
 * @author caojiayuan
 */
class JsonValidator
{
  protected $tempPath;

  protected $typeAlias = [
    'int' => 'integer'
  ];


  public function __construct($config)
  {
    $this->tempPath = $config['template_path'];
  }


  public function validate($data, $template)
  {
    $templateData = $this->getTemplateData($template);

    return $this->validateData($data, $templateData);
  }

  public function validateData($data, $templateData, $prev = null)
  {
    $result = [];

    if (is_string($templateData)) {
      if (!$this->validateType($data, explode('|', $templateData))) {
        throw new ValidationException('*', ValidationException::TYPE_TYPE, $data, $templateData);
      }
      return $data;
    }

    foreach ($templateData as $key => $type) {
      if ($key == '*') {
        if (!is_array($data)) {
          throw new ValidationException($prev, ValidationException::TYPE_TYPE, $data);
        }
        foreach ($data as $k => $item) {
          $result[$k] = $this->validateData($item, $type, $key);
        }
        return $result;
      }

      list($key, $optional, $nullable) = $this->parseKey($key);

      if (!is_array($data)) {
        throw new ValidationException($prev, ValidationException::TYPE_TYPE, $data);
      }

      if (array_key_exists($key, $data)) {
        $dataValue = $data[$key];
        if ($nullable && is_null($dataValue)) {
          $result[$key] = null;
        } else {
          if (is_array($type)) {
            if (!is_array($data[$key])) {
              throw new ValidationException($key, ValidationException::TYPE_TYPE, $data);
            }

            $result[$key] = $this->validateData($dataValue, $type, $key);
          } else {
            if (!$this->validateType($dataValue, explode('|', $type))) {
              throw new ValidationException($key, ValidationException::TYPE_TYPE, $dataValue, $type);
            }
            $result[$key] = $dataValue;
          }
        }
      } else if (!$optional) {
        throw new ValidationException($key, ValidationException::TYPE_REQUIRED, $data);
      }
    }

    return $result;
  }

  protected function parseKey($key)
  {
    $optional = false;
    $nullable = false;
    $match = [];
    $matchNull = [];
    if (preg_match('/^\((.*?)\)$/',$key, $match)) {
      $key = $match[1];
      $optional = true;
    }

    if (preg_match('/^\[(.*?)\]$/',$key, $matchNull)) {
      $key = $matchNull[1];
      $nullable = true;
    }

    return [$key, $optional, $nullable];
  }

  public function validateType($value, array $exceptTypes)
  {
    if (in_array('any', $exceptTypes)) {
      return true;
    }

    if (in_array($this->getValueType($value), $this->resolveTypes($exceptTypes))) {
      return true;
    }

    return false;
  }

  protected function resolveTypes($types)
  {
    $ts = [];
    foreach ($types as $type) {
      $ts[] = $type;
      if (array_key_exists($type, $this->typeAlias)) {
        $ts[] = $this->typeAlias[$type];
      }
    }

    return $ts;
  }

  protected function getValueType($value)
  {
    if (is_null($value)) {
      return 'null';
    }

    return gettype($value);
  }

  protected function getTemplateData($template)
  {
    $path = $this->tempPath . DIRECTORY_SEPARATOR . $template . '.json';

    return json_decode(file_get_contents($path), true);
  }
}