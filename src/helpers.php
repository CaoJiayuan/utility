<?php

namespace CaoJiayuan\Utility;

function use_as_callable($value)
{
    if (is_callable($value)) {
        return $value;
    }


    return function () use ($value) {
        return $value;
    };
}

function use_as_closure($value)
{
    if (is_callable($value)) {
        return function () use ($value) {
            return call_user_func_array($value, func_get_args());
        };
    }

    return function () use ($value) {
        return $value;
    };
}


function array_remove_empty($array, $remove = ['', null])
{
    $removed = [];
    foreach ((array)$array as $key => $item) {
        if (!in_array($item, $remove)) {
            $removed[$key] = $item;
        }
    }
    return $removed;
}


function object_to_array($object)
{
    $result = [];
    $data = $object;
    if (is_object($data)) {
        $data = get_object_vars($data);
    }
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $res = null;
            $node = object_to_array($value);
            if (($key == '@attributes') && ($key)) {
                $result = $node;
            } else {
                $result[$key] = $node;
            }
        }
    } else {
        $result = $data;
    }
    return $result;
}

function xml_to_array($xml)
{
    libxml_disable_entity_loader(true);
    $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $values;
}

/**
 * @param string|array $file
 * @param callable $closure
 * @param bool $recursive
 */
function file_map($file, callable $closure, $recursive = true)
{
    foreach ((array)$file as $fe) {
        if (is_dir($fe)) {
            $items = new \FilesystemIterator($fe);
            /** @var \SplFileInfo $item */
            foreach ($items as $item) {
                if ($item->isDir() && !$item->isLink() && $recursive) {
                    $closure($item->getPathname(), $item, true);
                    file_map($item->getPathname(), $closure);
                } else {
                    $closure($item->getPathname(), $item, $item->isDir());
                }
            }
        } else {
            $f = new \SplFileInfo($fe);
            $closure($fe, $f, false);
        }
    }
}

function node_flatten($input, $nodeKey = 'nodes')
{
    $flattened = [];
    foreach ($input as $item) {
        array_push($flattened, array_except($item, $nodeKey));
        $nodes = array_get($item, $nodeKey, []);
        if ($nodes) {
            $flattened = array_merge($flattened, node_flatten($nodes, $nodeKey));
        }
    }

    return $flattened;
}

function serial_number($num, $length = 4, $prepend = '0')
{
    return sprintf("%'{$prepend}{$length}d", $num);
}

function array_string($array){
    if (!is_array($array)) {
        if (is_string($array)) {
            return "'$array'";
        }

        return $array;
    }
    $str = '[';
    $components = [];
    foreach ($array as  $key => $v){
        if (is_string($key)) {
            $key = "'$key'";
        }
        if (is_numeric($key)) {
            $components[] = array_string($v);
        } else {
            $components[] = "$key => " . array_string($v);
        }
    }

    $str .= implode(',', $components);
    $str .= ']';
    return $str;
}
