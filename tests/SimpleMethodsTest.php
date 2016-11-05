<?php

namespace Tests\Collection;

use Collection\Collection;

/**
 * Class SimpleMethodsTest
 */
class SimpleMethodsTest extends \PHPUnit_Framework_TestCase {

	public function dataProviderForSimpleMethods() {
		$data = [];

		for ($i = 1; $i <= 2; ++$i) {
			$model = new \stdClass();

			$model->user_id = $i;
			$model->name    = "Name$i";

			$data[] = $model;
		}

		$data[] = [
			'user_id' => 3,
			'name'    => 'Name3'
		];

		$unknownTypeData   = $data;
		$unknownTypeData[] = true;

		$errorInstanceData   = $data;
		$errorInstanceData[] = new \ReflectionClass(new \stdClass());

		return [
			'Unknown type of item' => [
				'data'      => $unknownTypeData,
				'primary'   => 'user_id',
				'exception' => 'Unknown type of item: boolean'
			],
			'Error model instance' => [
				'data'      => $errorInstanceData,
				'primary'   => 'user_id',
				'exception' => 'Model must be an instance of stdClass'
			],
			'Error set primary'    => [
				'data'      => $data,
				'primary'   => 'primary_property',
				'exception' => 'Property primary_property can\'t been used as a primary'
			],
			'New collection'       => [
				'data'      => $data,
				'primary'   => 'name',
				'exception' => null
			]
		];
	}

	/**
	 * Test simple methods of collection
	 *
	 * @dataProvider dataProviderForSimpleMethods
	 *
	 * @param array         $data
	 * @param string        $primary
	 * @param string | null $exception
	 */
	public function testSimpleMethods($data, $primary, $exception) {
		if ($exception !== null) {
			$this->setExpectedException('\Exception', $exception);
		}

		$collection = new Collection($data, $primary);

		$this->assertFalse($collection->isEmpty());
		$this->assertEquals(3, $collection->count());
		$this->assertEquals(['Name1', 'Name2', 'Name3'], $collection->getKeys());
		$this->assertEquals('name', $collection->getPrimary());
		$this->assertTrue($collection->issetItem('Name2'));
		$this->assertNull($collection->get('ErrorKey'));
		$this->assertEquals($data[0], $collection->get('Name1'));

		$modelToSet          = new \stdClass();
		$modelToSet->user_id = 4;
		$modelToSet->name    = "Name4";

		$collection->set('Name2', $modelToSet);
		$this->assertEquals($modelToSet, $collection->get('Name2'));

		$collection->setPrimary('user_id');

		$collection->seek(1);
		$this->assertEquals($modelToSet, $collection->current());
		$this->assertEquals($modelToSet->user_id, $collection->key());
		$this->assertTrue($collection->valid());

		$collection->seek(10);
		$this->assertNull($collection->current());

		$collection->rewind();
		$this->assertEquals($data[0], $collection->current());
		$this->assertEquals($data[0]->user_id, $collection->key());
		$this->assertTrue($collection->valid());

		$collection->next();
		$this->assertEquals($modelToSet, $collection->current());
		$this->assertEquals($modelToSet->user_id, $collection->key());
		$this->assertTrue($collection->valid());

		$collection->clear();
		$this->assertEquals([], $collection->getKeys());
		$this->assertEquals([], $collection->getData());
	}
}
