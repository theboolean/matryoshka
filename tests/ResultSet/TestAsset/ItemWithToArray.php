<?php
/**
 * Matryoshka
 *
 * @link        https://github.com/matryoshka-model/matryoshka
 * @copyright   Copyright (c) 2014-2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaTest\Model\ResultSet\TestAsset;

/**
 * Class ItemWithToArray
 */
class ItemWithToArray extends \ArrayObject
{
    public function toArray()
    {
        return $this->getArrayCopy();
    }
}
