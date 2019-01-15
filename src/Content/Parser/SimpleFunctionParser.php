<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/12/20
 * Time: 11:20
 */

namespace CaoJiayuan\Utility\Content\Parser;


class SimpleFunctionParser
{

    public function parse($line)
    {
        $array = explode(':', $line);

        $fn = $array[0];
        $paramLine = $array[1] ?? false;

        return [$fn, $paramLine ? explode(',', $paramLine) : []];
    }
}
