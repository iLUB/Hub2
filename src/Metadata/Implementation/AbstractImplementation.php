<?php

namespace SRAG\Plugins\Hub2\Metadata\Implementation;

use SRAG\Plugins\Hub2\Metadata\IMetadata;

/**
 * Class CustomMetadata
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractImplementation implements IMetadataImplementation {

	/**
	 * @var int
	 */
	private $ilias_id;
	/**
	 * @var IMetadata
	 */
	private $metadata;


	/**
	 * UDF constructor.
	 *
	 * @param $metadata
	 */
	public function __construct(IMetadata $metadata, int $ilias_id) {
		$this->metadata = $metadata;
		$this->ilias_id = $ilias_id;
	}


	/**
	 * @inheritDoc
	 */
	abstract public function write();


	/**
	 * @inheritDoc
	 */
	abstract public function read();


	/**
	 * @inheritDoc
	 */
	public function getMetadata(): IMetadata {
		return $this->metadata;
	}


	/**
	 * @inheritDoc
	 */
	public function getIliasId(): int {
		return $this->ilias_id;
	}
}
