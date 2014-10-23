<?php
/**
 * Matryoshka
 *
 * @link        https://github.com/matryoshka-model/matryoshka
 * @copyright   Copyright (c) 2014, Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Object;

use Matryoshka\Model\Exception;
use Matryoshka\Model\ModelAwareInterface;
use Matryoshka\Model\ModelInterface;
use Matryoshka\Model\AbstractModel;
use Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria;
use Matryoshka\Model\ModelAwareTrait;

/**
 *
 *
 */
abstract class AbstractActiveRecord extends AbstractObject implements
    ModelAwareInterface,
    ActiveRecordInterface
{

    use ModelAwareTrait;

    /**
     * @var AbstractCriteria
     */
    protected $activeRecordCriteriaPrototype;

    /**
     * Set Active Record Criteria Prototype
     *
     * @param AbstractCriteria $criteria
     * @return $this
     */
    public function setActiveRecordCriteriaPrototype(AbstractCriteria $criteria)
    {
        $this->activeRecordCriteriaPrototype = $criteria;
        return $this;
    }


    /**
     * Set Model
     *
     * @param ModelInterface $model
     * @return $this
     */
    public function setModel(ModelInterface $model)
    {
        if (!$model instanceof AbstractModel) {
            throw new Exception\InvalidArgumentException(
                'AbstractModel required in order to work with ActiveRecord'
            );
        }
        $this->model = $model;
        return $this;
    }

    /**
     * Save
     *
     * @return null|int
     * @throws Exception\RuntimeException
     */
    public function save()
    {
        if (!$this->activeRecordCriteriaPrototype) {
            throw new Exception\RuntimeException('An Active Record Criteria Prototype must be set prior to calling save()');
        }

        if (!$this->getModel()) {
            throw new Exception\RuntimeException('A Model must be set prior to calling save()');
        }

        $criteria = clone $this->activeRecordCriteriaPrototype;
        $criteria->setId($this->getId());
        $result = $this->getModel()->save($criteria, $this);
        return $result;
    }

    /**
     * Delete
     *
     * @return null|int
     * @throws Exception\RuntimeException
     */
    public function delete()
    {
        if (!$this->getId()) {
            throw new Exception\RuntimeException('An ID must be set prior to calling delete()');
        }

        if (!$this->activeRecordCriteriaPrototype) {
            throw new Exception\RuntimeException('An Active Record Criteria Prototype must be set prior to calling delete()');
        }

        if (!$this->getModel()) {
            throw new Exception\RuntimeException('A Model must be set prior to calling delete()');
        }

        $criteria = clone $this->activeRecordCriteriaPrototype;
        $criteria->setId($this->getId());
        $result = $this->getModel()->delete($criteria);
        return $result;
    }

    /**
     * Get
     *
     * @param $name
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function __get($name)
    {
        throw new Exception\InvalidArgumentException('Not a valid field in this object: ' . $name);
    }

    /**
     * Set
     *
     * @param string $name
     * @param mixed $value
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function __set($name, $value)
    {
        throw new Exception\InvalidArgumentException('Not a valid field in this object: ' . $name);
    }

    /**
     * Unset
     *
     * @param string $name
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function __unset($name)
    {
        throw new Exception\InvalidArgumentException('Not a valid field in this object: ' . $name);
    }
}