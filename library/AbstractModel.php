<?php
/**
 * Matryoshka
 *
 * @link        https://github.com/matryoshka-model/matryoshka
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model;

use Matryoshka\Model\Criteria\DeletableCriteriaInterface;
use Matryoshka\Model\Criteria\PaginableCriteriaInterface;
use Matryoshka\Model\Criteria\ReadableCriteriaInterface;
use Matryoshka\Model\Criteria\WritableCriteriaInterface;
use Matryoshka\Model\Exception;
use Matryoshka\Model\ResultSet\ResultSetInterface;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterAwareTrait;
use Zend\Paginator\AdapterAggregateInterface as PaginatorAdapterAggregateInterface;
use Zend\Stdlib\Hydrator\AbstractHydrator;
use Zend\Stdlib\Hydrator\HydratorAwareInterface;
use Zend\Stdlib\Hydrator\HydratorAwareTrait;

/**
 * Class AbstractModel
 */
abstract class AbstractModel implements
    ModelInterface,
    HydratorAwareInterface,
    InputFilterAwareInterface,
    PaginatorAdapterAggregateInterface
{
    use HydratorAwareTrait;
    use InputFilterAwareTrait;
    use DataGatewayAwareTrait;

    /**
     * @var PaginableCriteriaInterface
     */
    protected $paginatorCriteria;

    /**
     * ResultSet Prototype
     *
     * @var ResultSetInterface
     */
    protected $resultSetPrototype;

    /**
     * {@inheritdoc}
     */
    public function getHydrator()
    {
        if (!$this->hydrator && $this->getObjectPrototype() instanceof HydratorAwareInterface) {
            $this->hydrator = $this->getObjectPrototype()->getHydrator();
        }
        return $this->hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            if ($this->getObjectPrototype() instanceof InputFilterAwareInterface) {
                $this->inputFilter = $this->getObjectPrototype()->getInputFilter();
            } else {
                throw new Exception\RuntimeException(
                    'InputFilter must be set or the object prototype must be an instance of InputFilterAwareInterface'
                );
            }
        }
        return $this->inputFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function getResultSetPrototype()
    {
        return $this->resultSetPrototype;
    }

    /**
     * Set ResultSet Prototype
     *
     * @param ResultSetInterface $resultSet
     * @return $this
     */
    protected function setResultSetPrototype(ResultSetInterface $resultSet)
    {
        $this->resultSetPrototype = $resultSet;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectPrototype()
    {
        $resultSetPrototype = $this->getResultSetPrototype();
        if ($resultSetPrototype && ($objectPrototype = $resultSetPrototype->getObjectPrototype())) {
            if ($objectPrototype instanceof ModelAwareInterface) {
                $objectPrototype->setModel($this);
            }
            return $objectPrototype;
        }

        throw new Exception\RuntimeException(
            'ResultSet must be set and must have an object prototype'
        );
    }

    /**
     * Create
     *
     * @return object
     */
    public function create()
    {
        return clone $this->getObjectPrototype();
    }

    /**
     * Find
     *
     * @param ReadableCriteriaInterface $criteria
     * @return ResultSetInterface
     */
    public function find(ReadableCriteriaInterface $criteria)
    {
        // Ensure that object and resultset prototypes have been set
        $this->getObjectPrototype();

        $result = $criteria->apply($this);
        $resultSet = clone $this->getResultSetPrototype();
        $resultSet->initialize($result);
        return $resultSet;
    }

    /**
     * Save
     *
     * Inserts or updates data
     *
     * @param WritableCriteriaInterface $criteria
     * @param HydratorAwareInterface|object|array $dataOrObject
     * @throws Exception\RuntimeException
     * @return null|int
     */
    public function save(WritableCriteriaInterface $criteria, $dataOrObject)
    {
        if ($dataOrObject instanceof ModelAwareInterface) {
            $dataOrObject->setModel($this);
        }

        $hydrator = $this->getHydrator();

        if (!$hydrator && ($dataOrObject instanceof HydratorAwareInterface)) {
            $hydrator = $dataOrObject->getHydrator();
        }

        if ($hydrator && is_object($dataOrObject)) {
            $data = $hydrator->extract($dataOrObject);
        } else {

            if (is_array($dataOrObject)) {
                $data = $dataOrObject;
            } elseif (method_exists($dataOrObject, 'toArray')) {
                $data = $dataOrObject->toArray();
            } elseif (method_exists($dataOrObject, 'getArrayCopy')) {
                $data = $dataOrObject->getArrayCopy();
            } else {
                throw new Exception\RuntimeException(
                    'dataOrObject with type ' .
                    gettype($dataOrObject) .
                    ' cannot be casted to an array'
                );
            }

            if ($hydrator) {
                if (!$hydrator instanceof AbstractHydrator) {
                    throw new Exception\RuntimeException(
                        'Hydrator must be an instance of AbstractHydrator' .
                        'in order to extract single value with extractValue method'
                    );
                }
                $data = [];
                foreach ($dataOrObject as $key => $value) {
                    $data[$key] = $hydrator->extractValue($key, $value, $dataOrObject);
                }

            }
        }

        $result = $criteria->applyWrite($this, $data);

        if (!is_integer($result)) {
            $result = null;
        }

        if ($result && $hydrator && is_object($dataOrObject)) {
            $hydrator->hydrate($data, $dataOrObject);
        }

        return $result;
    }

    /**
     * Delete
     *
     * @param DeletableCriteriaInterface $criteria
     * @return null|int
     */
    public function delete(DeletableCriteriaInterface $criteria)
    {
        $result = $criteria->applyDelete($this);
        if (!is_integer($result)) {
            $result = null;
        }
        return $result;
    }


    /**
     * Set the default paginator criteria
     *
     * @param PaginableCriteriaInterface $criteria
     * @return $this
     */
    public function setPaginatorCriteria(PaginableCriteriaInterface $criteria)
    {
        $this->paginatorCriteria = $criteria;
        return $this;
    }

    /**
     * Retrive the default paginator criteria
     *
     * @throws Exception\RuntimeException
     * @return PaginableCriteriaInterface
     */
    public function getPaginatorCriteria()
    {
        if (!$this->paginatorCriteria) {
            throw new Exception\RuntimeException('PaginatorCriteria must be set before use');
        }

        return $this->paginatorCriteria;
    }

    /**
     * {@inheritdoc}
     * @throws Exception\RuntimeException
     */
    public function getPaginatorAdapter(PaginableCriteriaInterface $criteria = null)
    {
        if (null === $criteria) {
            $criteria = $this->getPaginatorCriteria();
        }

        return $criteria->getPaginatorAdapter($this);
    }
}