<?php
/**
 * Matryoshka
 *
 * @link        https://github.com/matryoshka-model/matryoshka
 * @copyright   Copyright (c) 2014, Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\ResultSet;

use ArrayIterator;
use Iterator;
use IteratorAggregate;
use Matryoshka\Model\Exception;

/**
 * Class AbstractResultSet
 */
abstract class AbstractResultSet implements Iterator, ResultSetInterface
{
    /**
     * @var Iterator|IteratorAggregate
     */
    protected $dataSource = null;

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var int
     */
    protected $count = null;

    /**
     * Set the data source for the result set
     *
     * @param  Iterator|IteratorAggregate $dataSource
     * @return ResultSetInterface
     * @throws Exception\InvalidArgumentException
     */
    public function initialize($dataSource)
    {
        $this->position = 0;
        $this->count    = null;

        if (is_array($dataSource)) {
            reset($dataSource);
            $this->dataSource = new ArrayIterator($dataSource);
        } elseif ($dataSource instanceof IteratorAggregate) {
            $this->dataSource = $dataSource->getIterator();
            $this->dataSource->rewind();
        } elseif ($dataSource instanceof Iterator) {
            $this->dataSource = $dataSource;
            $this->dataSource->rewind();
        } else {
            throw new Exception\InvalidArgumentException('DataSource provided is not an array, nor does it implement Iterator or IteratorAggregate');
        }

        return $this;
    }

    /**
     * Get the data source used to create the result set
     *
     * @return null|Iterator
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * Iterator: move pointer to next item
     *
     * @return void
     */
    public function next()
    {
        $this->dataSource->next();
        $this->position++;
    }

    /**
     * Iterator: retrieve current key
     *
     * @return mixed
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Iterator: get current item
     *
     * @return array
     */
    public function current()
    {
        return $this->dataSource->current();
    }

    /**
     * Iterator: is pointer valid?
     *
     * @return bool
     */
    public function valid()
    {
        return $this->dataSource->valid();
    }

    /**
     * Iterator: rewind
     *
     * @return void
     */
    public function rewind()
    {
        $this->dataSource->rewind();
        $this->position = 0;
    }

    /**
     * Countable: return count of items
     *
     * @return int
     * @throws Exception\RuntimeException
     */
    public function count()
    {
        if ($this->count === null) {
            if ($this->dataSource instanceof \Countable) {
                $this->count = $this->dataSource->count();
            } elseif (is_array($this->dataSource)) {
                $this->count = count($this->dataSource);
            } else {
                throw new Exception\RuntimeException('DataSource with type ' . gettype($this->dataSource) . ' cannot be counted');
            }
        }
        return $this->count;
    }

    /**
     * Cast result set to array of arrays
     *
     * @return array
     * @throws Exception\RuntimeException if any item is not castable to an array
     */
    public function toArray()
    {
        $return = [];
        foreach ($this as $item) {
            if (is_array($item)) {
                $return[] = $item;
            } elseif (method_exists($item, 'toArray')) {
                $return[] = $item->toArray();
            } elseif (method_exists($item, 'getArrayCopy')) {
                $return[] = $item->getArrayCopy();
            } else {
                throw new Exception\RuntimeException(
                    'Items as part of this DataSource, with type ' . gettype($item) . ' cannot be cast to an array'
                );
            }
        }
        return $return;
    }
}
