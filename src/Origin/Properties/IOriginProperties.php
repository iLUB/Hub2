<?php namespace SRAG\Plugins\Hub2\Origin\Properties;

/**
 * Interface Properties
 *
 * @package SRAG\Plugins\Hub2\Origin\Properties
 */
interface IOriginProperties {

	const PREFIX_UPDATE_DTO = 'update_dto_';


	/**
	 * Get a property value by key, returns NULL if no property is found.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get($key);


	/**
	 * Checks if the given property of a DTO object should be updated on the ILIAS object,
	 * e.g. the first- or lastname of a user.
	 *
	 * TODO Better documentation
	 *
	 * @param string $property
	 *
	 * @return bool
	 */
	public function updateDTOProperty($property);


	/**
	 * Get all properties as associative array
	 *
	 * @return array
	 */
	public function getData();


	/**
	 * Set all properties as associative array
	 *
	 * @param array $data
	 *
	 * @return $this
	 */
	public function setData(array $data);
}