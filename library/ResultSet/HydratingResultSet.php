<?php
/**
 * Matryoshka
 *
 * @link        https://github.com/matryoshka-model/matryoshka
 * @copyright   Copyright (c) 2014-2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\ResultSet;

use ArrayObject;
use Matryoshka\Model\Exception;
use Matryoshka\Model\Object\PrototypeStrategy\PrototypeStrategyAwareInterface;
use Matryoshka\Model\Object\PrototypeStrategy\PrototypeStrategyAwareTrait;
use Zend\Stdlib\Hydrator\ArraySerializable;
use Zend\Stdlib\Hydrator\HydratorAwareInterface;
use Zend\Stdlib\Hydrator\HydratorAwareTrait;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Class HydratingResultSet
 *
 * A more flexible AbstractResultSet implementation that allows to choose an appropriate <b>hydration strategy</b>
 * for getting data into a target object.
 */
class HydratingResultSet extends AbstractResultSet implements
    HydratingResultSetInterface,
    PrototypeStrategyAwareInterface
{
    use HydratorAwareTrait;
    use PrototypeStrategyAwareTrait;

    /**
     * @var object
     */
    protected $objectPrototype = null;

    /**
     * Constructor
     *
     * @param  null|HydratorInterface $hydrator
     * @param  null|object $objectPrototype
     */
    public function __construct(HydratorInterface $hydrator = null, $objectPrototype = null)
    {
        if (!$hydrator && $objectPrototype instanceof HydratorAwareInterface) {
            $hydrator = $objectPrototype->getHydrator();
        }
        $this->setHydrator(($hydrator) ?: new ArraySerializable);
        $this->setObjectPrototype(($objectPrototype) ?: new ArrayObject([], ArrayObject::ARRAY_AS_PROPS));
    }

    /**
     * Set the row object prototype
     *
     * @param  object $objectPrototype
     * @throws Exception\InvalidArgumentException
     * @return HydratingResultSet
     */
    public function setObjectPrototype($objectPrototype)
    {
        if (!is_object($objectPrototype)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Object prototype must be an object, given "%s"',
                    gettype($objectPrototype)
                )
            );
        }

        $this->objectPrototype = $objectPrototype;
        return $this;
    }

    /**
     * Get the item object prototype
     *
     * @return object
     */
    public function getObjectPrototype()
    {
        return $this->objectPrototype;
    }

    /**
     * Iterator: get current item
     *
     * @return object|null
     */
    public function current()
    {
        $data = $this->dataSource->current();
        $object = null;

        if (is_array($data)) {
            $object = $this->getPrototypeStrategy()->createObject($this->getObjectPrototype(), $data);
            $this->getHydrator()->hydrate($data, $object);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    protected function itemToArray($item)
    {
        return $this->getHydrator()->extract($item);
    }
}
