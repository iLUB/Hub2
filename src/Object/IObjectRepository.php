<?php namespace SRAG\Hub2\Object;

/**
 * Interface IObjectRepository
 * @package SRAG\Hub2\Object
 */
interface IObjectRepository {

	/**
	 * Return all objects
	 *
	 * @return IObject[]
	 */
	public function all();

	/**
	 * Return only the objects having the given status
	 *
	 * @param int $status
	 * @return IObject[]
	 */
	public function getByStatus($status);

	/**
	 * Return all objects where the status TO_DELETE should be applied.
	 * This method must return all hub objects where the ext-ID is not part of the given ext-IDs,
	 * e.g. SELECT * FROM x WHERE ext_id NOT IN ($ext_ids).
	 *
	 * @param array $ext_ids
	 * @return IObject[]
	 */
	public function getToDelete(array $ext_ids);

	/**
	 * Return the number of objects
	 *
	 * @return int
	 */
	public function count();

}