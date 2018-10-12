<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/10/11
 * Time: 下午3:56
 */

namespace CaoJiayuan\Utility\Content\Matcher;

class SimpleParamMatcher
{
    protected $matchPattern;

    public function __construct($matchPattern = '/{\s*([A-Za-z_\-\.0-9]+)\s*}/')
    {
        $this->matchPattern = $matchPattern;
    }

    public function match($template, $content)
    {
        $keys = [];
        $regex = preg_replace_callback($this->matchPattern, function ($match) use (&$keys) {
            $keys[] = $match[1];
            return '(.*?)';
        }, $template);
        if (preg_match("#^{$regex}$#", $content, $data)) {
            array_shift($data);
            $i = 0;
            $result = [];
            foreach($data as $item) {
                $result[$keys[$i]] = $item;
                $i++;
            }
            return $result;
        }

        return false;
    }
}
