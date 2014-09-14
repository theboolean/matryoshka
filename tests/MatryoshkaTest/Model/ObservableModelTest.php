<?php
/**
 * Matryoshka
 *
 * @link        https://github.com/matryoshka-model/matryoshka
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaTest\Model;

use Matryoshka\Model\ObservableModel;
use MatryoshkaTest\Model\TestAsset\ResultSet;

/**
 * Class ObservableModelTest
 */
class ObservableModelTest extends ModelTest
{
    /** @var ObservableModel */
    protected $model;

    public function setUp()
    {
        $this->mockDataGateway = $this->getMock('stdClass');

        $this->resultSet = new ResultSet();

        $this->model = new ObservableModel($this->mockDataGateway, $this->resultSet);
    }

    public function testConstructorDefaults()
    {
        $this->assertSame($this->resultSet, $this->model->getResultSetPrototype());
        $this->assertSame($this->mockDataGateway, $this->model->getDataGateway());
    }

}
