<?php
/**
 * Matryoshka
 *
 * @link        https://github.com/matryoshka-model/matryoshka
 * @copyright   Copyright (c) 2014-2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model;

/**
 * Interface DataGatewayAwareInterface
 */
interface DataGatewayAwareInterface
{

    /**
     * Set Data Gateway
     * @param mixed $dataGateway
     */
    public function setDataGateway($dataGateway);

    /**
     * Get Data Gateway
     * @return mixed
     */
    public function getDataGateway();
}
