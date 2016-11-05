<?php

namespace Collection;

/**
 * Class Collection
 *
 * @author  Vitaliy Panait <panait.vi@gmail.com>
 *
 * @package Collection
 */
class Collection implements \SeekableIterator, \ArrayAccess {

	/**
	 * Array of models
	 *
	 * @var array
	 */
	protected $_data = [];

	/**
	 * Array's keys of models
	 *
	 * @var array
	 */
	protected $_keys = [];

	/**
	 * Iterator
	 *
	 * @var int
	 */
	protected $_cursor;

	/**
	 * Primary property
	 *
	 * @var string
	 */
	protected $_primary;

	/**
	 * Class name
	 *
	 * @var string
	 */
	protected $_instance;

	/**
	 * Collection's factory
	 *
	 * @param array         $data
	 * @param string | null $primary
	 *
	 * @return static
	 */
	public static function factory($data, $primary = null) {
		return new static($data, $primary);
	}

	/**
	 * Constructor
	 *
	 * @param array      $data
	 * @param int | null $primary
	 *
	 * @throws \Exception
	 */
	public function __construct($data, $primary = null) {
		foreach ($data as $key => $item) {
			$item              = $this->_prepareData($item);
			$this->_data[$key] = $item;
		}

		unset($item);

		$this->_cursor = 0;
		$this->_keys   = array_keys($this->_data);

		if ($primary !== null) {
			$this->setPrimary($primary);
		}
	}

	/**
	 * Set a property as primary
	 *
	 * @param string $name
	 *
	 * @return self
	 * @throws \Exception
	 */
	public function setPrimary($name) {
		$tempData    = $this->_data;
		$this->_data = [];

		foreach ($tempData as $item) {
			if (! isset($item->{$name})) {
				throw new \Exception("Property $name can't been used as a primary");
			}

			$this->_data[$item->{$name}] = $item;
		}

		unset($item, $tempData);

		$this->_cursor  = 0;
		$this->_keys    = array_keys($this->_data);
		$this->_primary = $name;

		return $this;
	}

	/**
	 * Get an item of collection by key
	 *
	 * @param string $key
	 *
	 * @return object | null
	 */
	public function get($key) {
		return $this->issetItem($key) ? $this->_data[$key] : null;
	}

	/**
	 * Set an item by key
	 *
	 * @param string         $key
	 * @param object | array $item
	 *
	 * @return self
	 * @throws \Exception
	 */
	public function set($key, $item) {
		$item = $this->_prepareData($item);

		if (! $this->issetItem($key)) {
			$this->_keys[] = $key;
		}

		$this->_data[$key] = $item;

		return $this;
	}


	/**
	 * Add an item
	 *
	 * @param object | array $item
	 *
	 * @return self
	 * @throws \Exception
	 */
	public function add($item) {
		$item = $this->_prepareData($item);

		if ($this->_primary === null) {
			$this->_data[] = $item;

			end($this->_data);
			$this->_keys[] = key($this->_data);
		} else {
			if (! isset($item->{$this->_primary})) {
				throw new \Exception("Primary {$this->_primary} not found");
			}

			$id = $item->{$this->_primary};

			$this->_keys[]    = $id;
			$this->_data[$id] = $item;
		}

		return $this;
	}

	/**
	 * Get array of values by keys
	 *
	 * @param string        $propertyValue
	 * @param bool          $isPrimary
	 * @param bool          $isUnique
	 * @param string | null $propertyKey
	 *
	 * @return array
	 */
	public function getList($propertyValue, $isPrimary = false, $isUnique = false, $propertyKey = null) {
		$data = [];

		if ($isPrimary) {
			$propertyKey = $this->getPrimary();
		}

		if ($propertyKey === null) {
			foreach ($this->_keys as $key) {
				if (isset($this->_data[$key]->{$propertyValue})) {
					$data[] = $this->_data[$key]->{$propertyValue};
				}
			}
		} else {
			foreach ($this->_keys as $key) {
				if (isset($this->_data[$key]->{$propertyKey}, $this->_data[$key]->{$propertyValue})) {
					$data[$this->_data[$key]->{$propertyKey}] = $this->_data[$key]->{$propertyValue};
				}
			}
		}

		if ($isUnique) {
			return array_unique($data);
		}

		return $data;
	}

	/**
	 * Find an items by property
	 *
	 * @param string     $property
	 * @param mixed      $value
	 * @param int | null $limit
	 *
	 * @return Collection
	 */
	public function find($property, $value = true, $limit = null) {
		if ($limit === null) {
			$limit = $this->count();
		}

		$data    = $this->createClone();
		$isArray = is_array($value);

		foreach ($this->_keys as $key) {
			$item = $this->_data[$key];

			if ($limit < 1) {
				break;
			}

			if (isset($item->{$property}) && ($isArray ? in_array($item->{$property}, $value) : $item->{$property} == $value)) {
				$data->set($key, $item);

				--$limit;
			}
		}

		return $data;
	}

	/**
	 * Get slice of a collection
	 *
	 * @param int $size
	 * @param int $offset
	 *
	 * @return Collection
	 */
	public function slice($size, $offset = 0) {
		$data          = $this->createClone();
		$availableSize = $this->count() - $offset;

		if ($size > $availableSize) {
			$size = $availableSize;
		}

		$last = $size + $offset;

		for ($i = $offset; $i < $last; ++$i) {
			$key = $this->_keys[$i];

			$data->set($key, $this->_data[$key]);
		}

		return $data;
	}

	/**
	 * Merge a collection with current collection
	 *
	 * @param Collection $collection
	 *
	 * @return self
	 * @throws \Exception
	 */
	public function merge(Collection $collection) {
		if ($collection->isEmpty()) {
			return $this;
		}

		$primary   = $this->getPrimary();
		$isPrimary = $primary !== null;

		if ($isPrimary) {
			$sourcePrimary = $collection->getPrimary();

			if ($primary !== $sourcePrimary) {
				$collection->setPrimary($primary);
			}
		}

		$data        = $collection->getData();
		$this->_data = array_merge($this->_data, $data);

		if ($isPrimary) {
			$this->setPrimary($primary);
		}

		$this->_cursor = 0;
		$this->_keys   = array_keys($this->_data);

		return $this;
	}

	/**
	 * Create empty collection like current collection
	 *
	 * @param array $data
	 *
	 * @return Collection
	 */
	public function createClone($data = []) {
		$collection            = static::factory($data, $this->getPrimary());
		$collection->_instance = $this->_instance;

		return $collection;
	}

	/**
	 * Sort a collection by property
	 *
	 * @param string | null $property
	 * @param bool          $inverse
	 *
	 * @return self
	 * @throws \Exception
	 */
	public function sort($property = null, $inverse = false) {
		if ($property === null) {
			$property = $this->getPrimary();

			if ($property === null) {
				throw new \Exception('Primary property not found');
			}
		}

		$unsortedData = [];

		foreach ($this->_data as $key => $item) {
			$propertyKey                      = isset($item->{$property}) ? $item->{$property} : null;
			$unsortedData[$propertyKey][$key] = $item;
		}

		unset($item, $propKey);

		$inverse ? krsort($unsortedData) : ksort($unsortedData);

		$sortedData = [];

		foreach ($unsortedData as $group) {
			$sortedData = $sortedData + $group;
		}

		unset($group);

		$this->_data = $sortedData;
		$this->_keys = array_keys($sortedData);


		return $this;
	}

	/**
	 * Check if item exists
	 *
	 * @param  string | int $key
	 *
	 * @return bool
	 */
	public function issetItem($key) {
		return isset($this->_data[$key]);
	}

	/**
	 * Unset item from collection by key
	 *
	 * @param string | int $key
	 *
	 * @return self
	 * @throws \Exception
	 */
	public function unsetItem($key) {
		if ($this->issetItem($key)) {
			unset($this->_data[$key]);
			$this->_keys = array_keys($this->_data);
		}

		return $this;
	}

	/**
	 * Get a primary property
	 *
	 * @return string
	 */
	public function getPrimary() {
		return $this->_primary;
	}

	/**
	 * Get keys of collection
	 *
	 * @return array
	 */
	public function getKeys() {
		return $this->_keys;
	}

	/**
	 * Get count of items
	 *
	 * @return int
	 */
	public function count() {
		return count($this->_keys);
	}

	/**
	 * Check if collection is empty
	 *
	 * @return bool
	 */
	public function isEmpty() {
		return empty($this->_data);
	}

	/**
	 * Clear a collection
	 *
	 * @return self
	 */
	public function clear() {
		$this->_keys     = [];
		$this->_data     = [];
		$this->_cursor   = 0;
		$this->_instance = null;

		return $this;
	}

	/**
	 * Get a data of collection
	 *
	 * @return array
	 */
	public function getData() {
		return $this->_data;
	}

	/**
	 * Prepare item before add to a collection
	 *
	 * @param object | array $item
	 *
	 * @return object
	 * @throws \Exception
	 */
	private function _prepareData($item) {
		if (is_array($item)) {
			$item = (object) $item;
		} else if (! is_object($item)) {
			throw new \Exception('Unknown type of item: ' . gettype($item));
		}

		if ($this->_instance === null) {
			$this->_instance = get_class($item);
		} else if (! $item instanceof $this->_instance) {
			throw new \Exception("Model must be an instance of {$this->_instance}");
		}

		return $item;
	}

	public function seek($cursor) {
		$this->_cursor = (int) $cursor;
	}

	public function current() {
		if (isset($this->_keys[$this->_cursor])) {
			return $this->get($this->_keys[$this->_cursor]);
		}
	}

	public function next() {
		++$this->_cursor;
	}

	public function key() {
		return $this->_primary === null
			? $this->_cursor
			: $this->_data[$this->_keys[$this->_cursor]]->{$this->_primary};
	}

	public function valid() {
		return isset($this->_keys[$this->_cursor]);
	}

	public function rewind() {
		$this->_cursor = 0;
	}

	public function offsetGet($offset) {
		return $this->get($offset);
	}

	public function offsetSet($offset, $value){
		if ($offset === null) {
			$this->add($value);
		} else {
			$this->set($offset, $value);
		}
	}

	public function offsetExists($offset) {
		return $this->issetItem($offset);
	}

	public function offsetUnset($offset) {
		$this->unsetItem($offset);
	}
}
