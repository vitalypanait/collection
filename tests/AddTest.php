<?php

namespace Tests\Collection;

use Collection\Collection;

/**
 * Class AddTest
 */
class AddTest extends \PHPUnit_Framework_TestCase {

	public function addItemProvider() {
		$data = [];

		for ($i = 1; $i <= 2; ++$i) {
			$model = new \stdClass();

			$model->user_id = $i;
			$model->name    = "Name$i";

			$data[] = $model;
		}

		$errorItem       = new \stdClass();
		$errorItem->name = 'NameError';

		$successItem          = new \stdClass();
		$successItem->user_id = 3;
		$successItem->user_id = 'Name3';

		return [
			'Primary not found'                        => [
				'data'      => $data,
				'item'      => $errorItem,
				'primary'   => 'user_id',
				'exception' => 'Primary user_id not found'
			],
			'Add item to a collection without primary' => [
				'data'      => $data,
				'item'      => $successItem,
				'primary'   => null,
				'exception' => null
			],
			'Add item to a collection with primary'    => [
				'data'      => $data,
				'item'      => $successItem,
				'primary'   => 'user_id',
				'exception' => null
			]
		];
	}

	/**
	 * Test add method
	 *
	 * @dataProvider addItemProvider
	 *
	 * @param array         $data
	 * @param object        $item
	 * @param string | null $primary
	 * @param string | null $exception
	 */
	public function testAdd($data, $item, $primary, $exception) {
		if ($exception !== null) {
			$this->setExpectedException('\Exception', $exception);
		}

		$collection = new Collection($data, $primary);

		$this->assertEquals(2, $collection->count());

		$collection->add($item);

		$this->assertEquals(3, $collection->count());

		$collection->seek($collection->count() - 1);

		$this->assertEquals($item, $collection->current());
	}
}
