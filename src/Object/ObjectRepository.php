<?php namespace SRAG\Hub2\Object;

use SRAG\Hub2\Origin\IOrigin;

/**
 * Class ObjectRepository
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\Hub2\Object
 */
abstract class ObjectRepository implements IObjectRepository {

	/**
	 * @var IOrigin
	 */
	protected $origin;

	public function __construct(IOrigin $origin) {
		$this->origin = $origin;
	}

	/**
	 * @inheritdoc
	 */
	public function all() {
		$class = $this->getClass();
		return $class::get();
	}

	/**
	 * @inheritdoc
	 */
	public function getByStatus($status) {
		$class = $this->getClass();
		return $class::where([
				'origin_id' => $this->origin->getId(),
				'status' => (int)$status
			])->get();
	}

	/**
	 * @inheritdoc
	 */
	public function getToDelete(array $ext_ids) {
		$class = $this->getClass();
		return $class::where([
			'origin_id' => $this->origin->getId(),
			'status' => [IObject::STATUS_CREATED, IObject::STATUS_UPDATED],
			'ext_ID' => $ext_ids,
		], ['=', 'IN', 'NOT IN'])->get();
	}


	/**
	 * @inheritdoc
	 */
	public function count() {
		$class = $this->getClass();
		return $class::count();
	}

	/**
	 * Returns the active record class name for the origin
	 *
	 * @return string
	 */
	protected function getClass() {
		return 'AR' . ucfirst($this->origin->getObjectType());
	}

}