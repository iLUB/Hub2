<?php namespace SRAG\Plugins\Hub2\Origin;

use SRAG\Plugins\Hub2\Exception\BuildObjectsFailedException;
use SRAG\Plugins\Hub2\Exception\ConnectionFailedException;
use SRAG\Plugins\Hub2\Exception\ParseDataFailedException;
use SRAG\Plugins\Hub2\Object\HookObject;
use SRAG\Plugins\Hub2\Object\DTO\IDataTransferObject;
use SRAG\Plugins\Hub2\Object\SessionMembership\SessionMembershipDTO;
use SRAG\Plugins\Hub2\Origin\Properties\SessionOriginProperties;

/**
 * Class demoSessionMembership
 *
 * @package SRAG\Plugins\Hub2\Origin
 */
class demoSessionMembership extends AbstractOriginImplementation {

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
		$this->log()->write("This is a test-log entry");

		$this->data[] = $this->factory()
		                     ->sessionMembership(1, 6)
		                     ->setSessionIdType(SessionMembershipDTO::PARENT_ID_TYPE_EXTERNAL_EXT_ID)
		                     ->setSessionId(rand(1, 10))
		                     ->setRole(SessionMembershipDTO::ROLE_MEMBER);
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