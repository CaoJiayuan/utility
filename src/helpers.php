<?php


if (!function_exists('use_as_callable')) {
    function use_as_callable($value)
    {
        if (is_callable($value)) {
            return $value;
        }

        return function () use ($value) {
            return $value;
        };
    }
}

if (!function_exists('use_as_closure')) {
    function use_as_closure($value) {
        if (is_callable($value)) {
            return function () use ($value) {
                return call_user_func_array($value, func_get_args());
            };
        }

        return function () use ($value) {
            return $value;
        };
    }
}


if (!function_exists('array_remove_empty')) {
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
}


if (!function_exists('object_to_array')) {
    function object_to_array($object, &$result)
    {
        $data = $object;
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $res = null;
                object_to_array($value, $res);
                if (($key == '@attributes') && ($key)) {
                    $result = $res;
                } else {
                    $result[$key] = $res;
                }
            }
        } else {
            $result = $data;
        }
    }
}
if (!function_exists('xml_to_array')) {
    function xml_to_array($xml)
    {
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }
}

if (!function_exists('file_map')) {
    /**
     * @param string|array $file
     * @param callable $closure
     * @param bool $recursive
     */
    function file_map($file, callable $closure, $recursive = true)
    {
        foreach ((array)$file as $fe) {
            if (is_dir($fe)) {
                $items = new FilesystemIterator($fe);
                /** @var SplFileInfo $item */
                foreach ($items as $item) {
                    if ($item->isDir() && !$item->isLink() && $recursive) {
                        $closure($item->getPathname(), $item, true);
                        file_map($item->getPathname(), $closure);
                    } else {
                        $closure($item->getPathname(), $item, $item->isDir());
                    }
                }
            } else {
                $f = new SplFileInfo($fe);
                $closure($fe, $f, false);
            }
        }
    }
}

if (!function_exists('node_flatten')) {
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
}
