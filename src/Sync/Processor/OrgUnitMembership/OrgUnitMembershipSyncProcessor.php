<?php

namespace SRAG\Plugins\Hub2\Sync\Processor\OrgUnitMembership;

use ilObjectFactory;
use ilObjOrgUnit;
use ilOrgUnitUserAssignment;
use SRAG\Plugins\Hub2\Exception\HubException;
use SRAG\Plugins\Hub2\Helper\DIC;
use SRAG\Plugins\Hub2\Log\ILog;
use SRAG\Plugins\Hub2\Notification\OriginNotifications;
use SRAG\Plugins\Hub2\Object\DTO\IDataTransferObject;
use SRAG\Plugins\Hub2\Object\ObjectFactory;
use SRAG\Plugins\Hub2\Object\OrgUnitMembership\IOrgUnitMembershipDTO;
use SRAG\Plugins\Hub2\Origin\Config\IOrgUnitMembershipOriginConfig;
use SRAG\Plugins\Hub2\Origin\IOrigin;
use SRAG\Plugins\Hub2\Origin\IOriginImplementation;
use SRAG\Plugins\Hub2\Origin\OriginFactory;
use SRAG\Plugins\Hub2\Origin\Properties\IOrgUnitMembershipOriginProperties;
use SRAG\Plugins\Hub2\Sync\IObjectStatusTransition;
use SRAG\Plugins\Hub2\Sync\Processor\FakeIliasObject;
use SRAG\Plugins\Hub2\Sync\Processor\ObjectSyncProcessor;

/**
 * Class OrgUnitMembershipSyncProcessor
 *
 * @package SRAG\Plugins\Hub2\Sync\Processor\OrgUnitMembership
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class OrgUnitMembershipSyncProcessor extends ObjectSyncProcessor implements IOrgUnitMembershipSyncProcessor {

	use DIC;
	/**
	 * @var IOrgUnitMembershipOriginProperties
	 */
	private $props;
	/**
	 * @var IOrgUnitMembershipOriginConfig
	 */
	private $config;
	/**
	 * @var array
	 */
	protected static $properties = [];


	/**
	 * @param IOrigin                 $origin
	 * @param IOriginImplementation   $implementation
	 * @param IObjectStatusTransition $transition
	 * @param ILog                    $originLog
	 * @param OriginNotifications     $originNotifications
	 */
	public function __construct(IOrigin $origin, IOriginImplementation $implementation, IObjectStatusTransition $transition, ILog $originLog, OriginNotifications $originNotifications) {
		parent::__construct($origin, $implementation, $transition, $originLog, $originNotifications);
		$this->props = $origin->properties();
		$this->config = $origin->config();
	}


	/**
	 * @return array
	 */
	public static function getProperties(): array {
		return self::$properties;
	}


	/**
	 * @param IOrgUnitMembershipDTO $dto
	 *
	 * @return FakeIliasObject
	 * @throws HubException
	 */
	protected function handleCreate(IDataTransferObject $dto): FakeIliasObject {
		return $this->getFakeIliasObject($this->assignToOrgUnit($dto));
	}


	/**
	 * @param IOrgUnitMembershipDTO $dto
	 * @param int                   $ilias_id
	 *
	 * @return FakeIliasObject
	 * @throws HubException
	 */
	protected function handleUpdate(IDataTransferObject $dto, $ilias_id): FakeIliasObject {
		$this->handleDelete($ilias_id);

		return $this->handleCreate($dto);
	}


	/**
	 * @param int $ilias_id
	 *
	 * @return FakeIliasObject
	 * @throws HubException
	 */
	protected function handleDelete($ilias_id): FakeIliasObject {
		$ilias_object = FakeOrgUnitMembershipObject::loadInstanceWithConcatenatedId($ilias_id);

		$assignment = ilOrgUnitUserAssignment::where([
			"orgu_id" => $ilias_object->getContainerIdIlias(),
			"user_id" => $ilias_object->getUserIdIlias(),
			"position_id" => $ilias_object->getPositionId()
		])->first();

		if ($assignment !== NULL) {
			$assignment->delete();
		}

		return $ilias_object;
	}


	/**
	 * @param IOrgUnitMembershipDTO $dto
	 *
	 * @return ilOrgUnitUserAssignment
	 * @throws HubException
	 */
	protected function assignToOrgUnit(IOrgUnitMembershipDTO $dto): ilOrgUnitUserAssignment {
		switch ($dto->getPosition()) {
			case IOrgUnitMembershipDTO::POSITION_EMPLOYEE:
				$position_id = self::IL_POSITION_EMPLOYEE;
				break;

			case IOrgUnitMembershipDTO::POSITION_SUPERIOR:
				$position_id = self::IL_POSITION_SUPERIOR;
				break;

			default:
				throw new HubException("Invalid position {$dto->getPosition()}!");
				break;
		}

		return ilOrgUnitUserAssignment::findOrCreateAssignment($dto->getUserId(), $position_id, $this->getOrgUnitId($dto));
	}


	/**
	 * @param ilOrgUnitUserAssignment $assignment
	 *
	 * @return FakeIliasObject
	 * @throws HubException
	 */
	protected function getFakeIliasObject(ilOrgUnitUserAssignment $assignment): FakeIliasObject {
		return new FakeOrgUnitMembershipObject($assignment->getOrguId(), $assignment->getUserId(), $assignment->getPositionId());
	}


	/**
	 * @param IOrgUnitMembershipDTO $dto
	 *
	 * @return int
	 * @throws HubException
	 */
	protected function getOrgUnitId(IOrgUnitMembershipDTO $dto): int {
		switch ($dto->getOrgUnitIdType()) {
			case IOrgUnitMembershipDTO::ORG_UNIT_ID_TYPE_EXTERNAL_EXT_ID:
				$ext_id = $dto->getOrgUnitId();

				$linkedOriginId = $this->config->getLinkedOriginId();
				if (!$linkedOriginId) {
					throw new HubException("Unable to lookup external ref-ID because there is no origin linked");
				}

				$origin_factory = new OriginFactory($this->db());
				$origin = $origin_factory->getById($linkedOriginId);

				$object_factory = new ObjectFactory($origin);

				$org_unit = $object_factory->orgUnit($ext_id);

				$org_unit_id = $org_unit->getILIASId();

				if ($org_unit_id === NULL) {
					throw new HubException("External ID {$ext_id} not found!");
				}
				break;

			case IOrgUnitMembershipDTO::ORG_UNIT_ID_TYPE_REF_ID:
			default:
				$org_unit_id = $dto->getOrgUnitId();
				break;
		}

		$org_unit = $this->getOrgUnitObject($org_unit_id);
		if ($org_unit === NULL) {
			throw new HubException("Org Unit {$org_unit_id} not found!");
		}

		return $org_unit->getRefId();
	}


	/**
	 * @param int $obj_id
	 *
	 * @return ilObjOrgUnit|null
	 */
	protected function getOrgUnitObject(int $obj_id) {
		$ref_id = current(ilObjOrgUnit::_getAllReferences($obj_id));
		if (!$ref_id) {
			return NULL;
		}

		$orgUnit = ilObjectFactory::getInstanceByRefId($ref_id);

		if ($orgUnit !== false && $orgUnit instanceof ilObjOrgUnit) {
			return $orgUnit;
		} else {
			return NULL;
		}
	}
}
