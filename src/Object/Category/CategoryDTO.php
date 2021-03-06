<?php namespace SRAG\Plugins\Hub2\Object\Category;

use SRAG\Plugins\Hub2\Object\DTO\DataTransferObject;
use SRAG\Plugins\Hub2\Object\DTO\ITaxonomyAndMetadataAwareDataTransferObject;
use SRAG\Plugins\Hub2\Object\DTO\TaxonomyAndMetadataAwareDataTransferObject;

/**
 * Class CategoryDTO
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class CategoryDTO extends DataTransferObject implements ITaxonomyAndMetadataAwareDataTransferObject {

	use TaxonomyAndMetadataAwareDataTransferObject;
	const ORDER_TYPE_TITLE = 0;
	const ORDER_TYPE_MANUAL = 1;
	const ORDER_TYPE_ACTIVATION = 2;
	const ORDER_TYPE_INHERIT = 3;
	const ORDER_TYPE_CREATION = 4;
	const PARENT_ID_TYPE_REF_ID = 1;
	const PARENT_ID_TYPE_EXTERNAL_EXT_ID = 2;
	/**
	 * @var array
	 */
	private static $orderTypes = [
		self::ORDER_TYPE_TITLE,
		self::ORDER_TYPE_MANUAL,
		self::ORDER_TYPE_ACTIVATION,
		self::ORDER_TYPE_INHERIT,
		self::ORDER_TYPE_CREATION,
	];
	/**
	 * @var array
	 */
	private static $parentIdTypes = [
		self::PARENT_ID_TYPE_REF_ID,
		self::PARENT_ID_TYPE_EXTERNAL_EXT_ID,
	];
	/**
	 * @var string
	 */
	protected $title;
	/**
	 * @var string
	 */
	protected $description;
	/**
	 * @var int
	 */
	protected $orderType = self::ORDER_TYPE_TITLE;
	/**
	 * @var int
	 */
	protected $owner = 6;
	/**
	 * @var string
	 */
	private $parentId;
	/**
	 * @var int
	 */
	private $parentIdType = self::PARENT_ID_TYPE_REF_ID;
	/**
	 * @var bool
	 */
	protected $showNews = true;
	/**
	 * @var bool
	 */
	protected $showInfoPage = true;


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @param string $title
	 *
	 * @return CategoryDTO
	 */
	public function setTitle($title) {
		$this->title = $title;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}


	/**
	 * @param string $description
	 *
	 * @return CategoryDTO
	 */
	public function setDescription($description) {
		$this->description = $description;

		return $this;
	}


	/**
	 * @return int
	 */
	public function getOrderType() {
		return $this->orderType;
	}


	/**
	 * @param int $orderType
	 *
	 * @return CategoryDTO
	 */
	public function setOrderType($orderType) {
		if (!in_array($orderType, self::$orderTypes)) {
			throw new \InvalidArgumentException("Given '$orderType' is not a valid order type'");
		}
		$this->orderType = $orderType;

		return $this;
	}


	/**
	 * @return int
	 */
	public function getOwner() {
		return $this->owner;
	}


	/**
	 * @param int $owner
	 *
	 * @return CategoryDTO
	 */
	public function setOwner($owner) {
		$this->owner = $owner;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getParentId() {
		return $this->parentId;
	}


	/**
	 * @param int $parentId
	 *
	 * @return $this
	 */
	public function setParentId($parentId) {
		$this->parentId = $parentId;

		return $this;
	}


	/**
	 * @return int
	 */
	public function getParentIdType() {
		return $this->parentIdType;
	}


	/**
	 * @param int $parentIdType
	 *
	 * @return CategoryDTO
	 */
	public function setParentIdType($parentIdType) {
		if (!in_array($parentIdType, self::$parentIdTypes)) {
			throw new \InvalidArgumentException("Invalid parentIdType given '$parentIdType'");
		}
		$this->parentIdType = $parentIdType;

		return $this;
	}


	/**
	 * @return bool
	 */
	public function isShowNews() {
		return $this->showNews;
	}


	/**
	 * @param bool $showNews
	 *
	 * @return CategoryDTO
	 */
	public function setShowNews($showNews) {
		$this->showNews = $showNews;

		return $this;
	}


	/**
	 * @return bool
	 */
	public function isShowInfoPage() {
		return $this->showInfoPage;
	}


	/**
	 * @param bool $showInfoPage
	 *
	 * @return CategoryDTO
	 */
	public function setShowInfoPage($showInfoPage) {
		$this->showInfoPage = $showInfoPage;

		return $this;
	}
}