<?php

namespace SRAG\Plugins\Hub2\Origin;

use SRAG\Plugins\Hub2\Exception\BuildObjectsFailedException;
use SRAG\Plugins\Hub2\Exception\ConnectionFailedException;
use SRAG\Plugins\Hub2\Exception\ParseDataFailedException;
use SRAG\Plugins\Hub2\Object\Course\CourseDTO;
use SRAG\Plugins\Hub2\Object\HookObject;
use SRAG\Plugins\Hub2\Object\DTO\IDataTransferObject;

/**
 * Class demoCourse
 *
 * @package SRAG\Plugins\Hub2\Origin
 */
class demoCourse extends AbstractOriginImplementation {

	/**
	 * @var array
	 */
	protected $data = [];


	/**
	 * Connect to the service providing the sync data.
	 * Throw a ConnectionFailedException to abort the sync if a connection is not possible.
	 *
	 * @throws ConnectionFailedException
	 * @return bool
	 */
	public function connect() {
		return true;
	}


	/**
	 * Parse and prepare (sanitize/validate) the data to fill the DTO objects.
	 * Return the number of data. Note that this number is used to check if the amount of delivered
	 * data is sufficent to continue the sync, depending on the configuration of the origin.
	 *
	 * Throw a ParseDataFailedException to abort the sync if your data cannot be parsed.
	 *
	 * @throws ParseDataFailedException
	 * @return int
	 */
	public function parseData() {
		$this->log()->write("This is a demo");
		$time = time();
		for ($x = 1; $x <= 10; $x ++) {
			$this->data[] = $this->factory()
			                     ->course($x)
			                     ->setTitle("Title {$x} {$time}")
			                     ->setDescription("Description {$x}")
			                     ->setActivationType(CourseDTO::ACTIVATION_OFFLINE)
			                     ->setOwner(6)
			                     ->setContactEmail("Email {$x}")
			                     ->setContactName("Name {$x}")
			                     ->setParentId(1)
			                     ->setParentIdType(CourseDTO::PARENT_ID_TYPE_REF_ID)
			                     ->setViewMode(CourseDTO::VIEW_MODE_BY_TYPE)
			                     ->setSyllabus("Syllabus {$x}")
			                     ->addMetadata($this->metadata()
			                                        ->getDTOWithIliasId(1)
			                                        ->setValue("Meine Metadaten {$time}"))
			                     ->addTaxonomy($this->taxonomy()
			                                        ->create("Taxonomy 1")
			                                        ->attach($this->taxonomy()
			                                                      ->node("Node Title 1.1"))
			                                        ->attach($this->taxonomy()
			                                                      ->node("Node Title 1.2")))
			                     ->addTaxonomy($this->taxonomy()
			                                        ->create("Taxonomy 2")
			                                        ->attach($this->taxonomy()
			                                                      ->node("Node Title 2.1"))
			                                        ->attach($this->taxonomy()
			                                                      ->node("Node Title 2.2")));
		}

		return count($this->data);
	}


	/**
	 * Build the hub DTO objects from the parsed data.
	 * An instance of such objects MUST be obtained over the DTOObjectFactory. The factory
	 * is available via $this->factory().
	 *
	 * Example for an origin syncing users:
	 *
	 * $user = $this->factory()->user($data->extId) {   }
	 * $user->setFirstname($data->firstname)
	 *  ->setLastname($data->lastname)
	 *  ->setGender(UserDTO::GENDER_FEMALE) {   }
	 *
	 * Throw a BuildObjectsFailedException to abort the sync at this stage.
	 *
	 * @throws BuildObjectsFailedException
	 * @return IDataTransferObject[]
	 */
	public function buildObjects() {
		return $this->data;
	}


	// HOOKS
	// ------------------------------------------------------------------------------------------------------------

	/**
	 * Called if any exception occurs during processing the ILIAS objects. This hook can be used to
	 * influence the further processing of the current origin sync or the global sync:
	 *
	 * - Throw an AbortOriginSyncException to stop the current sync of this origin.
	 *   Any other following origins in the processing chain are still getting executed normally.
	 * - Throw an AbortOriginSyncOfCurrentTypeException to abort the current sync of the origin AND
	 *   all also skip following syncs from origins of the same object type, e.g. User, Course etc.
	 * - Throw an AbortSyncException to stop the global sync. The sync of any other following
	 * origins in the processing chain is NOT getting executed.
	 *
	 * Note that if you do not throw any of the exceptions above, the sync will continue.
	 *
	 * @param \Exception $e
	 */
	public function handleException(\Exception $e) { }


	/**
	 * @param HookObject $object
	 */
	public function beforeCreateILIASObject(HookObject $object) { }


	/**
	 * @param HookObject $object
	 */
	public function afterCreateILIASObject(HookObject $object) { }


	/**
	 * @param HookObject $object
	 */
	public function beforeUpdateILIASObject(HookObject $object) { }


	/**
	 * @param HookObject $object
	 */
	public function afterUpdateILIASObject(HookObject $object) { }


	/**
	 * @param HookObject $object
	 */
	public function beforeDeleteILIASObject(HookObject $object) { }


	/**
	 * @param HookObject $object
	 */
	public function afterDeleteILIASObject(HookObject $object) { }


	/**
	 * Executed before the synchronization of the origin is executed.
	 */
	public function beforeSync() { }


	/**
	 * Executed after the synchronization of the origin has been executed.
	 */
	public function afterSync() { }
}