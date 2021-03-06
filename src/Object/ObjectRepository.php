<?php namespace SRAG\Plugins\Hub2\Object;

use SRAG\Plugins\Hub2\Origin\IOrigin;

/**
 * Class ObjectRepository
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\Plugins\Hub2\Object
 */
abstract class ObjectRepository implements IObjectRepository {

	/**
	 * @var IOrigin
	 */
	protected $origin;
	/**
	 * @var array
	 */
	protected static $classmap = [];


	/**
	 * ObjectRepository constructor.
	 *
	 * @param \SRAG\Plugins\Hub2\Origin\IOrigin $origin
	 */
	public function __construct(IOrigin $origin) {
		$this->origin = $origin;
	}


	/**
	 * @inheritdoc
	 */
	public function all() {
		$class = $this->getClass();
		/** @var $class \ActiveRecord */
		return $class::where([ 'origin_id' => $this->origin->getId() ])->get();
	}


	/**
	 * @inheritdoc
	 */
	public function getByStatus($status) {
		$class = $this->getClass();
		/** @var $class \ActiveRecord */
		return $class::where([
			'origin_id' => $this->origin->getId(),
			'status'    => (int)$status,
		])->get();
	}


	/**
	 * @inheritdoc
	 */
	public function getToDelete(array $ext_ids) {
		$class = $this->getClass();

		if (count($ext_ids) > 0) {
			/** @var $class \ActiveRecord */
			return $class::where([
				'origin_id' => $this->origin->getId(),
				// We only can transmit from final states CREATED and UPDATED to TO_DELETE
				// E.g. not from DELETED or IGNORED
				'status'    => [ IObject::STATUS_CREATED, IObject::STATUS_UPDATED ],
				'ext_id'    => $ext_ids,
			], [ 'origin_id' => '=', 'status' => 'IN', 'ext_id' => 'NOT IN' ])->get();
		} else {
			/** @var $class \ActiveRecord */
			return $class::where([
				'origin_id' => $this->origin->getId(),
				// We only can transmit from final states CREATED and UPDATED to TO_DELETE
				// E.g. not from DELETED or IGNORED
				'status'    => [ IObject::STATUS_CREATED, IObject::STATUS_UPDATED ],
			], [ 'origin_id' => '=', 'status' => 'IN' ])->get();
		}
	}


	/**
	 * @inheritdoc
	 */
	public function count() {
		$class = $this->getClass();
		/** @var $class \ActiveRecord */
		return $class::where([ 'origin_id' => $this->origin->getId() ])->count();
	}


	/**
	 * Returns the active record class name for the origin
	 *
	 * @return string
	 */
	protected function getClass() {
		$object_type = $this->origin->getObjectType();

		if (isset(self::$classmap[$object_type])) {
			return self::$classmap[$object_type];
		}

		$ucfirst = ucfirst($object_type);
		self::$classmap[$object_type] = "SRAG\\Plugins\\Hub2\\Object\\" . $ucfirst . "\\AR"
		                                . $ucfirst;

		return self::$classmap[$object_type];
	}
}