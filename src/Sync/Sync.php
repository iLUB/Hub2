<?php namespace SRAG\Plugins\Hub2\Sync;

use SRAG\Plugins\Hub2\Exception\AbortOriginSyncOfCurrentTypeException;
use SRAG\Plugins\Hub2\Exception\AbortSyncException;
use SRAG\Plugins\Hub2\Origin\IOrigin;

/**
 * Class Sync
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\Hub2\Sync
 */
class Sync implements ISync {

	/**
	 * @var IOrigin[]
	 */
	protected $origins = [];
	/**
	 * @var \Exception[] array
	 */
	protected $exceptions = [];
	/**
	 * @var OriginSync[] array
	 */
	protected $originSyncs = [];


	/**
	 * Execute the syncs of the given origins.
	 *
	 * Note: This class assumes that the origins are in the correct order, e.g. as returned by
	 * OriginRepository::allActive() --> [users > categories > courses > courseMemberships...]
	 *
	 * @param IOrigin[] $origins
	 */
	public function __construct($origins) {
		$this->origins = $origins;
	}


	/**
	 * @inheritdoc
	 */
	public function execute() {
		$skip_object_type = '';
		foreach ($this->origins as $origin) {
			if ($origin->getObjectType() == $skip_object_type) {
				continue;
			}
			$originSyncFactory = new OriginSyncFactory($origin);
			$originSync = $originSyncFactory->instance();
			try {
				$originSync->execute();
			} catch (AbortSyncException $e) {
				// This must abort the global sync, none following origin syncs are executed
				$this->exceptions = array_merge($this->exceptions, $originSync->getExceptions());
				break;
			} catch (AbortOriginSyncOfCurrentTypeException $e) {
				// This must abort all following origin syncs of the same object type
				$skip_object_type = $origin->getObjectType();
			} catch (\Exception $e) {
				// Any other exception means that we abort the current origin sync and continue with the next origin
				$this->exceptions[] = $e;
			} catch (\Throwable $e) {
				// Any other exception means that we abort the current origin sync and continue with the next origin
				$this->exceptions[] = $e;
			}
			$this->exceptions = array_merge($this->exceptions, $originSync->getExceptions());
		}
	}


	/**
	 * @inheritdoc
	 */
	public function getExceptions() {
		return $this->exceptions;
	}
}