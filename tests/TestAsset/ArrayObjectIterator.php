<?php
namespace MatryoshkaTest\Model\TestAsset;

class ArrayObjectIterator implements \Iterator
{

    private $var = array();

    public function __construct($array)
    {
        if (is_array($array)) {
            $this->var = $array;
        }
    }

    public function rewind()
    {
        reset($this->var);
    }

    public function current()
    {
        return current($this->var);
    }

    public function key()
    {
        return key($this->var);
    }

    public function next()
    {
        return next($this->var);
    }

    public function valid()
    {
        $key = key($this->var);
        $var = ($key !== null && $key !== false);
        return $var;
    }
}
