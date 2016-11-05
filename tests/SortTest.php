<?php

namespace Tests\Collection;

use Collection\Collection;

/**
 * Class SortTest
 */
class SortTest extends \PHPUnit_Framework_TestCase {

	private $_data = [];

	public function setUp() {
		for ($i = 11; $i <= 14; ++$i) {
			$model = new \stdClass();

			$model->user_id = $i;

			if ($i !== 13) {
				$model->name = "Name$i";
			}

			$this->_data[] = $model;
		}

	}

	public function sortProvider() {
		return [
			'Property is null and primary is null'     => [
				'property'  => null,
				'inverse'   => true,
				'primary'   => null,
				'exception' => 'Primary property not found',
				'result'    => null
			],
			'Property is null and primary is not null' => [
				'property'  => null,
				'inverse'   => true,
				'primary'   => 'user_id',
				'exception' => null,
				'result'    => [14, 13, 12, 11]
			],
			'Property is not null' => [
				'property'  => 'name',
				'inverse'   => false,
				'primary'   => 'user_id',
				'exception' => null,
				'result'    => [13, 11, 12, 14]
			]
		];
	}

	/**
	 * Test Find method
	 *
	 * @dataProvider sortProvider
	 *
	 * @param string | null $property
	 * @param bool          $inverse
	 * @param string | null $primary
	 * @param string | null $exception
	 * @param array  | null $result
	 */
	public function testSort($property, $inverse, $primary, $exception, $result) {
		if ($exception !== null) {
			$this->setExpectedException('\Exception', $exception);
		}

		$collection = new Collection($this->_data, $primary);
		$collection->sort($property, $inverse);

		$this->assertEquals($result, $collection->getKeys());
	}

	public function tearDown() {
		unset($this->_data);
	}
}
