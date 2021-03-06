<?php namespace SRAG\Plugins\Hub2\Sync\Processor;

use SRAG\Plugins\Hub2\Object\DTO\IDataTransferObject;
use SRAG\Plugins\Hub2\Object\IObject;

/**
 * Interface ObjectProcessor
 *
 * @package SRAG\Plugins\Hub2\Sync\Processor
 */
interface IObjectSyncProcessor {

	// This prefix MUST be used by the processors when setting the import ID on the ILIAS objects
	const IMPORT_PREFIX = 'srhub_';


	/**
	 * Process the given hub object and its corresponding DTO:
	 *
	 * 1. Depending on the status: Create, Update or Delete corresponding ILIAS object
	 * 2. Execute other actions based on the configuration of the origin.
	 * 3. Pass the DTO to the hooks of the origin
	 *
	 * @param IObject             $object
	 * @param IDataTransferObject $dto
	 */
	public function process(IObject $object, IDataTransferObject $dto);
}