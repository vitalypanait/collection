<?php

namespace Tests\Collection;

use Collection\Collection;

/**
 * Class MergeTest
 */
class MergeTest extends \PHPUnit_Framework_TestCase {

	public function mergeProvider() {
		$data = [];

		for ($i = 11; $i <= 12; ++$i) {
			$model = new \stdClass();

			$model->user_id = $i;
			$model->name    = "Name$i";

			$data[] = $model;
		}

		$model = new \stdClass();

		$model->user_id = 13;
		$model->name    = "Name13";

		$mergeData[] = $model;

		return [
			'Empty merge collection'                                   => [
				'data'         => $data,
				'primary'      => 'user_id',
				'mergeData'    => [],
				'mergePrimary' => 'user_id',
				'result'       => [11, 12]
			],
			'Primary is null'                                          => [
				'data'         => $data,
				'primary'      => null,
				'mergeData'    => $mergeData,
				'mergePrimary' => 'user_id',
				'result'       => [0, 1, 2]
			],
			'Primary collection is not equal primary merge collection' => [
				'data'         => $data,
				'primary'      => 'user_id',
				'mergeData'    => $mergeData,
				'mergePrimary' => 'name',
				'result'       => [11, 12, 13]
			]
		];
	}

	/**
	 * Test merge method
	 *
	 * @dataProvider mergeProvider
	 *
	 * @param array         $data
	 * @param string | null $primary
	 * @param array         $mergeData
	 * @param string        $mergePrimary
	 * @param array         $result
	 */
	public function testMerge($data, $primary, $mergeData, $mergePrimary, $result) {
		$collection    = new Collection($data, $primary);
		$newCollection = new Collection($mergeData, $mergePrimary);

		$collection->merge($newCollection);

		$this->assertEquals(count($result), $collection->count());
		$this->assertEquals($result, $collection->getKeys());
	}
}
