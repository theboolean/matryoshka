<?php
/**
 * Matryoshka
 *
 * @link        https://github.com/matryoshka-model/matryoshka
 * @copyright   Copyright (c) 2014-2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Hydrator\Strategy;

use Matryoshka\Model\Exception;
use Zend\Stdlib\Hydrator\HydratorAwareInterface;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Class HasOneStrategy
 */
class HasOneStrategy implements StrategyInterface, NullableStrategyInterface
{
    use NullableStrategyTrait;

    /**
     * @var HydratorAwareInterface
     */
    protected $objectPrototype;

    /**
     * Ctor
     *
     * @param HydratorAwareInterface $objectPrototype
     * @param bool $nullable
     */
    public function __construct(HydratorAwareInterface $objectPrototype, $nullable = true)
    {
        $this->objectPrototype = $objectPrototype;
        $this->setNullable($nullable);
    }

    /**
     * @return HydratorAwareInterface
     */
    public function getObjectPrototype()
    {
        return $this->objectPrototype;
    }

    /**
     * Converts the given value so that it can be extracted by the hydrator.
     *
     * @param object|array|null $value The original value.
     * @return array|null Returns the value that should be extracted.
     */
    public function extract($value)
    {
        if (null === $value) {
            return $this->nullable ? null : [];
        }

        if (is_array($value)) {
            return $value;
        }

        $objectPrototype = $this->getObjectPrototype();

        if ($value instanceof $objectPrototype) {
            return $objectPrototype->getHydrator()->extract($value);
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Invalid value: must be null (only if nullable option is enabled), or an array, or an instance of "%s": "%s" given',
            get_class($objectPrototype),
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }

    /**
     * Converts the given value so that it can be hydrated by the hydrator.
     *
     * @param object|array|null $value The original value.
     * @return object|null Returns the value that should be hydrated.
     */
    public function hydrate($value)
    {
        $objectPrototype = $this->getObjectPrototype();

        if (is_array($value)) {
            $object = clone $objectPrototype;
            return $object->getHydrator()->hydrate($value, $object);
        }

        if (null === $value) {
            return $this->nullable ? null : clone $objectPrototype;
        }

        if ($value instanceof $objectPrototype) {
            return clone $value;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Invalid value: must be null (only if nullable option is enabled), or an array, or an instance of "%s": "%s" given',
            get_class($objectPrototype),
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }
}
