<?php

namespace srag\Plugins\Hub2\Sync\Processor;

use ilHub2Plugin;
use srag\DIC\Hub2\DICTrait;
use srag\Plugins\Hub2\Notification\OriginNotifications;
use srag\Plugins\Hub2\Origin\IOrigin;
use srag\Plugins\Hub2\Origin\IOriginImplementation;
use srag\Plugins\Hub2\Sync\IObjectStatusTransition;
use srag\Plugins\Hub2\Sync\Processor\Category\CategorySyncProcessor;
use srag\Plugins\Hub2\Sync\Processor\Course\CourseActivities;
use srag\Plugins\Hub2\Sync\Processor\Course\CourseSyncProcessor;
use srag\Plugins\Hub2\Sync\Processor\CourseMembership\CourseMembershipSyncProcessor;
use srag\Plugins\Hub2\Sync\Processor\Group\GroupActivities;
use srag\Plugins\Hub2\Sync\Processor\Group\GroupSyncProcessor;
use srag\Plugins\Hub2\Sync\Processor\GroupMembership\GroupMembershipSyncProcessor;
use srag\Plugins\Hub2\Sync\Processor\OrgUnit\IOrgUnitSyncProcessor;
use srag\Plugins\Hub2\Sync\Processor\OrgUnit\OrgUnitSyncProcessor;
use srag\Plugins\Hub2\Sync\Processor\OrgUnitMembership\IOrgUnitMembershipSyncProcessor;
use srag\Plugins\Hub2\Sync\Processor\OrgUnitMembership\OrgUnitMembershipSyncProcessor;
use srag\Plugins\Hub2\Sync\Processor\Session\SessionSyncProcessor;
use srag\Plugins\Hub2\Sync\Processor\SessionMembership\SessionMembershipSyncProcessor;
use srag\Plugins\Hub2\Sync\Processor\User\UserSyncProcessor;
use srag\Plugins\Hub2\Utils\Hub2Trait;

/**
 * Class SyncProcessorFactory
 *
 * @package srag\Plugins\Hub2\Sync\Processor
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class SyncProcessorFactory implements ISyncProcessorFactory {

	use DICTrait;
	use Hub2Trait;
	const PLUGIN_CLASS_NAME = ilHub2Plugin::class;
	/**
	 * @var IOrigin
	 */
	protected $origin;
	/**
	 * @var IObjectStatusTransition
	 */
	protected $statusTransition;
	/**
	 * @var OriginNotifications
	 *
	 * @deprecated
	 */
	protected $originNotifications;
	/**
	 * @var IOriginImplementation
	 */
	protected $implementation;


	/**
	 * @param IOrigin                 $origin
	 * @param IOriginImplementation   $implementation
	 * @param IObjectStatusTransition $statusTransition
	 * @param OriginNotifications     $originNotifications
	 */
	public function __construct(IOrigin $origin, IOriginImplementation $implementation, IObjectStatusTransition $statusTransition, OriginNotifications $originNotifications) {
		$this->origin = $origin;
		$this->statusTransition = $statusTransition;
		$this->originNotifications = $originNotifications;
		$this->implementation = $implementation;
	}


	/**
	 * @inheritdoc
	 */
	public function user() {
		return new UserSyncProcessor($this->origin, $this->implementation, $this->statusTransition, $this->originNotifications);
	}


	/**
	 * @inheritdoc
	 */
	public function course() {
		return new CourseSyncProcessor($this->origin, $this->implementation, $this->statusTransition, $this->originNotifications, new CourseActivities(self::dic()
			->database()));
	}


	/**
	 * @inheritdoc
	 */
	public function category() {
		return new CategorySyncProcessor($this->origin, $this->implementation, $this->statusTransition, $this->originNotifications);
	}


	/**
	 * @inheritdoc
	 */
	public function session() {
		return new SessionSyncProcessor($this->origin, $this->implementation, $this->statusTransition, $this->originNotifications);
	}


	/**
	 * @inheritdoc
	 */
	public function courseMembership() {
		return new CourseMembershipSyncProcessor($this->origin, $this->implementation, $this->statusTransition, $this->originNotifications);
	}


	/**
	 * @inheritdoc
	 */
	public function group() {
		return new GroupSyncProcessor($this->origin, $this->implementation, $this->statusTransition, $this->originNotifications, new GroupActivities(self::dic()
			->database()));
	}


	/**
	 * @inheritdoc
	 */
	public function groupMembership() {
		return new GroupMembershipSyncProcessor($this->origin, $this->implementation, $this->statusTransition, $this->originNotifications);
	}


	/**
	 * @inheritdoc
	 */
	public function sessionMembership() {
		return new SessionMembershipSyncProcessor($this->origin, $this->implementation, $this->statusTransition, $this->originNotifications);
	}


	/**
	 * @inheritdoc
	 */
	public function orgUnit(): IOrgUnitSyncProcessor {
		return new OrgUnitSyncProcessor($this->origin, $this->implementation, $this->statusTransition, $this->originNotifications);
	}


	/**
	 * @inheritdoc
	 */
	public function orgUnitMembership(): IOrgUnitMembershipSyncProcessor {
		return new OrgUnitMembershipSyncProcessor($this->origin, $this->implementation, $this->statusTransition, $this->originNotifications);
	}
}
