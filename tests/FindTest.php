<?php

namespace Tests\Collection;

use Collection\Collection;

/**
 * Class FindTest
 */
class FindTest extends \PHPUnit_Framework_TestCase {

	private $_data = [];

	public function setUp() {
		$model          = new \stdClass();
		$model->user_id = 1;
		$model->city_id = 10;

		$this->_data[] = $model;

		$model          = new \stdClass();
		$model->user_id = 2;

		$this->_data[] = $model;

		$model          = new \stdClass();
		$model->user_id = 3;
		$model->city_id = 20;

		$this->_data[] = $model;

	}

	public function findProvider() {
		return [
			'Limit is null'     => [
				'limit'  => null,
				'result' => [1, 3]
			],
			'Limit is not null' => [
				'limit'  => 1,
				'result' => [1]
			]
		];
	}

	/**
	 * Test Find method
	 *
	 * @dataProvider findProvider
	 *
	 * @param int | null $limit
	 */
	public function testFind($limit, $result) {
		$collection = new Collection($this->_data);
		$collection->setPrimary('user_id');

		$newCollection = $collection->find('city_id', [10, 20], $limit);

		$this->assertEquals($result, $newCollection->getKeys());
	}

	public function tearDown() {
		unset($this->_data);
	}
}
