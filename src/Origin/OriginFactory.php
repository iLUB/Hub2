<?php namespace SRAG\Plugins\Hub2\Origin;

/**
 * Class OriginFactory
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\Plugins\Hub2\Origin
 */
class OriginFactory implements IOriginFactory {

	/**
	 * @var \ilDB
	 */
	private $db;


	/**
	 * @param \ilDBInterface $db
	 */
	public function __construct(\ilDBInterface $db) {
		$this->db = $db;
	}


	/**
	 * @inheritdoc
	 */
	public function getById($id) {
		$sql = 'SELECT object_type FROM sr_hub2_origin WHERE id = %s';
		$set = $this->db->queryF($sql, [ 'integer' ], [ $id ]);
		$type = $this->db->fetchObject($set)->object_type;
		$class = $this->getClass($type);

		return $class::find((int)$id);
	}


	/**
	 * @inheritdoc
	 */
	public function createByType($type) {
		$class = $this->getClass($type);

		return new $class();
	}


	/**
	 * @inheritdoc
	 */
	public function getAllActive() {
		$origins = [];

		$sql = 'SELECT id FROM sr_hub2_origin WHERE active = %s';
		$set = $this->db->queryF($sql, [ 'integer' ], [ 1 ]);
		while ($data = $this->db->fetchObject($set)) {
			$origins[] = $this->getById($data->id);
		}

		return $origins;
	}


	/**
	 * @param $type
	 *
	 * @return string
	 */
	protected function getClass($type) {
		$ucfirst = ucfirst($type);
		$class = "SRAG\\Plugins\\Hub2\\Origin\\{$ucfirst}\\AR{$ucfirst}Origin";

		return $class;
	}
}