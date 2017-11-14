<?php namespace SRAG\Plugins\Hub2\Sync\Processor;

use SRAG\Plugins\Hub2\Log\ILog;
use SRAG\Plugins\Hub2\Notification\OriginNotifications;
use SRAG\Plugins\Hub2\Origin\IOrigin;
use SRAG\Plugins\Hub2\Origin\IOriginImplementation;
use SRAG\Plugins\Hub2\Sync\IObjectStatusTransition;
use SRAG\Plugins\Hub2\Sync\Processor\Category\CategorySyncProcessor;
use SRAG\Plugins\Hub2\Sync\Processor\Course\CourseActivities;
use SRAG\Plugins\Hub2\Sync\Processor\Course\CourseSyncProcessor;
use SRAG\Plugins\Hub2\Sync\Processor\CourseMembership\CourseMembershipSyncProcessor;
use SRAG\Plugins\Hub2\Sync\Processor\Group\GroupActivities;
use SRAG\Plugins\Hub2\Sync\Processor\Group\GroupSyncProcessor;
use SRAG\Plugins\Hub2\Sync\Processor\Session\SessionSyncProcessor;
use SRAG\Plugins\Hub2\Sync\Processor\User\UserSyncProcessor;

/**
 * Class SyncProcessorFactory
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\Plugins\Hub2\Sync\Processor
 */
class SyncProcessorFactory implements ISyncProcessorFactory {

	/**
	 * @var IOrigin
	 */
	protected $origin;
	/**
	 * @var IObjectStatusTransition
	 */
	protected $statusTransition;
	/**
	 * @var ILog
	 */
	protected $originLog;
	/**
	 * @var OriginNotifications
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
	 * @param ILog                    $originLog
	 * @param OriginNotifications     $originNotifications
	 */
	public function __construct(IOrigin $origin, IOriginImplementation $implementation, IObjectStatusTransition $statusTransition, ILog $originLog, OriginNotifications $originNotifications) {
		$this->origin = $origin;
		$this->statusTransition = $statusTransition;
		$this->originLog = $originLog;
		$this->originNotifications = $originNotifications;
		$this->implementation = $implementation;
	}


	/**
	 * @inheritdoc
	 */
	public function user() {
		return new UserSyncProcessor($this->origin, $this->implementation, $this->statusTransition, $this->originLog, $this->originNotifications);
	}


	/**
	 * @inheritdoc
	 */
	public function course() {
		global $DIC;

		return new CourseSyncProcessor($this->origin, $this->implementation, $this->statusTransition, $this->originLog, $this->originNotifications, new CourseActivities($DIC->database()));
	}


	/**
	 * @inheritdoc
	 */
	public function category() {
		return new CategorySyncProcessor($this->origin, $this->implementation, $this->statusTransition, $this->originLog, $this->originNotifications);
	}

	/**
	 * @inheritdoc
	 */
	public function session() {
		return new SessionSyncProcessor($this->origin, $this->implementation, $this->statusTransition, $this->originLog, $this->originNotifications);
	}


	/**
	 * @inheritDoc
	 */
	public function courseMembership() {
		return new CourseMembershipSyncProcessor($this->origin, $this->implementation, $this->statusTransition, $this->originLog, $this->originNotifications);
	}


	/**
	 * @inheritDoc
	 */
	public function group() {
		global $DIC;

		return new GroupSyncProcessor($this->origin, $this->implementation, $this->statusTransition, $this->originLog, $this->originNotifications, new GroupActivities($DIC->database()));
	}
}