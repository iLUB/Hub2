<?php

namespace SRAG\Plugins\Hub2\Object\OrgUnit;

use SRAG\Plugins\Hub2\Object\DTO\DataTransferObject;

/**
 * Class OrgUnitDTO
 *
 * @package SRAG\Plugins\Hub2\Object\OrgUnit
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class OrgUnitDTO extends DataTransferObject implements IOrgUnitDTO {

	/**
	 * @var string
	 */
	protected $title = "";
	/**
	 * @var string
	 */
	protected $description = "";
	/**
	 * @var int
	 */
	protected $owner = 6;
	/**
	 * @var int
	 */
	protected $parent_id = 0;
	/**
	 * @var int
	 */
	protected $parent_id_type = self::PARENT_ID_TYPE_REF_ID;
	/**
	 * @var string
	 */
	protected $orgu_type = "";
	/**
	 * @var string
	 */
	protected $ext_id = "";


	/**
	 * @inheritDoc
	 */
	public function __construct(string $ext_id) {
		parent::__construct($ext_id);
		$this->ext_id = $ext_id;
	}


	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}


	/**
	 * @param string $title
	 *
	 * @return IOrgUnitDTO
	 */
	public function setTitle(string $title): IOrgUnitDTO {
		$this->title = $title;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}


	/**
	 * @param string $description
	 *
	 * @return IOrgUnitDTO
	 */
	public function setDescription(string $description): IOrgUnitDTO {
		$this->description = $description;

		return $this;
	}


	/**
	 * @return int
	 */
	public function getOwner(): int {
		return $this->owner;
	}


	/**
	 * @param int $owner
	 *
	 * @return IOrgUnitDTO
	 */
	public function setOwner(int $owner): IOrgUnitDTO {
		$this->owner = $owner;

		return $this;
	}


	/**
	 * @return int
	 */
	public function getParentId(): int {
		return $this->parent_id;
	}


	/**
	 * @param int $parent_id
	 *
	 * @return IOrgUnitDTO
	 */
	public function setParentId(int $parent_id): IOrgUnitDTO {
		$this->parent_id = $parent_id;

		return $this;
	}


	/**
	 * @return int
	 */
	public function getParentIdType(): int {
		return $this->parent_id_type;
	}


	/**
	 * @param int $parent__Id__type
	 *
	 * @return IOrgUnitDTO
	 */
	public function setParentIdType(int $parent__Id__type): IOrgUnitDTO {
		$this->parent_id_type = $parent__Id__type;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getOrguType(): string {
		return $this->orgu_type;
	}


	/**
	 * @param string $orgu_type
	 *
	 * @return IOrgUnitDTO
	 */
	public function setOrguType(string $orgu_type): IOrgUnitDTO {
		$this->orgu_type = $orgu_type;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getExtId(): string {
		return $this->ext_id;
	}


	/**
	 * @param string $ext_id
	 *
	 * @return IOrgUnitDTO
	 */
	public function setExtId(string $ext_id): IOrgUnitDTO {
		$this->ext_id = $ext_id;

		return $this;
	}
}
