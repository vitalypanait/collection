<?php

namespace Tests\Collection;

use Collection\Collection;

/**
 * Class UnsetItemTest
 */
class UnsetItemTest extends \PHPUnit_Framework_TestCase {

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

	public function unsetItemProvider() {
		return [
			'Item not found' => [
				'key'   => 'ErrorName',
				'count' => 2
			],
			'Unset success'  => [
				'key'   => 'Name1',
				'count' => 1
			]
		];
	}

	/**
	 * Test unsetItem method
	 *
	 * @dataProvider unsetItemProvider
	 *
	 * @param string $key
	 * @param int    $count
	 */
	public function testGetList($key, $count) {
		$collection = new Collection($this->_data, 'name');
		$collection->unsetItem($key);

		$this->assertEquals($count, $collection->count());
	}

	public function tearDown() {
		unset($this->_data);
	}
}
