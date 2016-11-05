<?php

namespace Tests\Collection;

use Collection\Collection;

/**
 * Class GetListTest
 */
class GetListTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var array
	 */
	private $_data = [];

	public function setUp() {
		for ($i = 1; $i <= 2; ++$i) {
			$model = new \stdClass();

			$model->user_id = "u$i";
			$model->age     = 20;
			$model->name    = "Name$i";

			$this->_data[] = $model;
		}
	}

	public function getListProvider() {
		return [
			'Primary is true'                                            => [
				'propertyValue' => 'name',
				'propertyKey'   => 'user_id',
				'isPrimary'     => true,
				'isUnique'      => false,
				'result'        => [
					'u1' => 'Name1',
					'u2' => 'Name2'
				]
			],
			'Primary is false, property key is null'                     => [
				'propertyValue' => 'name',
				'propertyKey'   => null,
				'isPrimary'     => false,
				'isUnique'      => false,
				'result'        => ['Name1', 'Name2']
			],
			'Primary is false, property key is not null'                 => [
				'propertyValue' => 'user_id',
				'propertyKey'   => 'name',
				'isPrimary'     => false,
				'isUnique'      => false,
				'result'        => [
					'Name1' => 'u1',
					'Name2' => 'u2'
				]
			],
			'Primary is false, property key is not null, unique is true' => [
				'propertyValue' => 'age',
				'propertyKey'   => null,
				'isPrimary'     => false,
				'isUnique'      => true,
				'result'        => [20]
			]
		];
	}

	/**
	 * Test getList method
	 *
	 * @dataProvider getListProvider
	 *
	 * @param string $propertyValue
	 * @param string $propertyKey
	 * @param bool   $isPrimary
	 * @param bool   $isUnique
	 * @param array  $result
	 */
	public function testGetList($propertyValue, $propertyKey, $isPrimary, $isUnique, $result) {
		$collection = new Collection($this->_data, $isPrimary ? $propertyKey : null);

		$this->assertEquals($result, $collection->getList($propertyValue, $isPrimary, $isUnique, $propertyKey));
	}

	public function tearDown() {
		unset($this->_data);
	}
}
