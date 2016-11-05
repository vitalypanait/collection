<?php

namespace Tests\Collection;

use Collection\Collection;

/**
 * Class SliceTest
 */
class SliceTest extends \PHPUnit_Framework_TestCase {

	public function sliceProvider() {
		$data = [];

		for ($i = 10; $i <= 12; ++$i) {
			$model = new \stdClass();

			$model->user_id = $i;
			$model->name    = "Name$i";

			$data[] = $model;
		}

		return [
			'Size > availableSize' => [
				'data'   => $data,
				'size'   => 10,
				'offset' => 0,
				'result' => [10, 11, 12]
			],
			'Offset = 0'           => [
				'data'   => $data,
				'size'   => 2,
				'offset' => 0,
				'result' => [10, 11]
			],
			'Offset > 0'           => [
				'data'   => $data,
				'size'   => 2,
				'offset' => 1,
				'result' => [11, 12]
			]
		];
	}

	/**
	 * Test slice method
	 *
	 * @dataProvider sliceProvider
	 *
	 * @param array $data
	 * @param int   $size
	 * @param int   $offset
	 * @param array $result
	 */
	public function testSlice($data, $size, $offset, $result) {
		$collection    = new Collection($data, 'user_id');
		$newCollection = $collection->slice($size, $offset);

		$this->assertEquals(count($result), $newCollection->count());
		$this->assertEquals($result, $newCollection->getKeys());
	}
}
