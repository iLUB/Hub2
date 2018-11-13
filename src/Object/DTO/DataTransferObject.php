<?php

namespace srag\Plugins\Hub2\Object\DTO;

use ilHub2Plugin;
use srag\DIC\Hub2\DICTrait;
use srag\Plugins\Hub2\Utils\Hub2Trait;

/**
 * Class ObjectDTO
 *
 * @package srag\Plugins\Hub2\Object\DTO
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class DataTransferObject implements IDataTransferObject {

	use DICTrait;
	use Hub2Trait;
	const PLUGIN_CLASS_NAME = ilHub2Plugin::class;
	/**
	 * @var string
	 */
	private $ext_id = '';
	/**
	 * @var string
	 */
	private $period = '';


	/**
	 * @param string $ext_id
	 */
	public function __construct($ext_id) {
		$this->ext_id = $ext_id;
	}


	/**
	 * @inheritdoc
	 */
	public function getExtId() {
		return $this->ext_id;
	}


	/**
	 * @inheritdoc
	 */
	public function getPeriod() {
		return $this->period;
	}


	/**
	 * @inheritdoc
	 */
	public function setPeriod($period) {
		$this->period = $period;

		return $this;
	}


	/**
	 * @inheritdoc
	 */
	public function getData() {
		$data = [];
		foreach ($this->getProperties() as $var) {
			$data[$var] = $this->{$var};
		}

		return $data;
	}


	/**
	 * @inheritdoc
	 */
	public function setData(array $data) {
		foreach ($data as $key => $value) {
			$this->{$key} = $value;
		}

		return $this;
	}


	/**
	 * @return array
	 */
	protected function getProperties() {
		return array_keys(get_class_vars(get_class($this)));
	}


	public function __toString() {
		return implode(', ', [
			"ext_id: " . $this->getExtId(),
			"period: " . $this->getPeriod(),
		]);
	}
}
