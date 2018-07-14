<?php namespace SRAG\Plugins\Hub2\Origin;

/**
 * Interface IOriginFactory
 *
 * @package SRAG\Plugins\Hub2\Origin
 */
interface IOriginFactory {

	/**
	 * Get the concrete origin by ID, e.g. returns a IUserOrigin if the given ID belongs
	 * to a origin of object type 'user'.
	 *
	 * @param int $id
	 *
	 * @return IOrigin|null
	 */
	public function getById($id); //Correct return type would by : ?IOrigin, but this is PHP7.1+


	/**
	 * @param string $type
	 *
	 * @return IOrigin
	 */
	public function createByType(string $type);


	/**
	 * @return IOrigin[]
	 */
	public function getAllActive(): array;
}