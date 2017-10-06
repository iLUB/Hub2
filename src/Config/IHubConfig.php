<?php

namespace SRAG\Hub2\Config;

/**
 * Interface IHubConfig
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\Hub2\Config
 */
interface IHubConfig {

	const ORIGIN_IMPLEMENTATION_PATH = 'origin_implementation_path';
	const SHORTLINK_NOT_FOUND = 'shortlink_not_found';
	const SHORTLINK_NO_ILIAS_ID = 'shortlink_no_ilias_id';
	const SHORTLINK_NOT_ACTIVE = 'shortlink_not_active';
	const ADMINISTRATE_HUB_ROLE_IDS = 'administrate_hub_role_ids';
	const LOCK_ORIGINS_CONFIG = 'lock_origins_config';


	/**
	 * Get the path to the class files of origin implementations
	 *
	 * @return string
	 */
	public function getOriginImplementationsPath();


	/**
	 * Get the message presented to the user if the ILIAS object was not found via shortlink.
	 *
	 * @return string
	 */
	public function getShortLinkNoObject();


	/**
	 * Get the message presented to the user if no ILIAS-ID is existing for the shortlink.
	 *
	 * @return string
	 */
	public function getShortLinkNoILIASId();


	/**
	 * Get the message presented to the user if the shortlink feature is not activated for the
	 * given shortlink.
	 *
	 * @return string
	 */
	public function getShortLinkNotActive();


	/**
	 * Get role IDs of ILIAS roles allowing to administrate hub, e.g. add and configure origins
	 *
	 * @return array
	 */
	public function getAdministrationRoleIds();


	/**
	 * Should settings of origins be locked?
	 *
	 * @return bool
	 */
	public function isOriginsConfigLocked();


	/**
	 * Get a config value by key.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get($key);
}