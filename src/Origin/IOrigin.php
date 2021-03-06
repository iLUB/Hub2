<?php namespace SRAG\Plugins\Hub2\Origin;

use SRAG\Plugins\Hub2\Origin\Config\IOriginConfig;
use SRAG\Plugins\Hub2\Origin\Properties\IOriginProperties;

/**
 * Interface Origin
 *
 * @package SRAG\ILIAS\Plugins\Hub2\Origin
 */
interface IOrigin {

	const OBJECT_TYPE_USER = 'user';
	const OBJECT_TYPE_COURSE_MEMBERSHIP = 'courseMembership';
	const OBJECT_TYPE_COURSE = 'course';
	const OBJECT_TYPE_CATEGORY = 'category';
	const OBJECT_TYPE_GROUP = 'group';
	const OBJECT_TYPE_GROUP_MEMBERSHIP = 'groupMembership';
	const OBJECT_TYPE_SESSION = 'session';
	const OBJECT_TYPE_SESSION_MEMBERSHIP = 'sessionMembership';
	const ORIGIN_MAIN_NAMESPACE = "SRAG\\Plugins\\Hub2\\Origin";


	/**
	 * @return int
	 */
	public function getId();


	/**
	 * @return string
	 */
	public function getTitle();


	/**
	 * @param string $title
	 *
	 * @return $this
	 */
	public function setTitle($title);


	/**
	 * @return string
	 */
	public function getDescription();


	/**
	 * @param string $description
	 *
	 * @return $this
	 */
	public function setDescription($description);


	/**
	 * @return bool
	 */
	public function isActive();


	/**
	 * @param bool $active
	 *
	 * @return $this
	 */
	public function setActive($active);


	/**
	 * @return string
	 */
	public function getImplementationClassName();


	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function setImplementationClassName($name);


	/**
	 * @return string
	 */
	public function getImplementationNamespace();


	/**
	 * @param string $implementation_namespace
	 */
	public function setImplementationNamespace($implementation_namespace);


	/**
	 * Get the object type that will be synced with this origin, e.g.
	 * user|course|category|courseMembership
	 *
	 * @return string
	 */
	public function getObjectType();


	/**
	 * @param string $type
	 *
	 * @return $this
	 */
	public function setObjectType($type);


	/**
	 * @return string
	 */
	public function getCreatedAt();


	/**
	 * @return string
	 */
	public function getUpdatedAt();


	/**
	 * Get access to all configuration data of this origin.
	 *
	 * @return IOriginConfig
	 */
	public function config();


	/**
	 * Get access to all properties data of this origin.
	 *
	 * @return IOriginProperties
	 */
	public function properties();


	/**
	 * @return string
	 */
	public function getLastRun();


	/**
	 * @param string $last_run
	 */
	public function setLastRun($last_run);


	public function update();


	public function create();
}