<?php

namespace SRAG\Plugins\Hub2\Exception;

use ilException;
use ilHub2Plugin;
use srag\DIC\DICTrait;

/**
 * Class HubException
 *
 * @package SRAG\Plugins\Hub2\Exception
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class HubException extends ilException {

	use DICTrait;
	const PLUGIN_CLASS_NAME = ilHub2Plugin::class;


	/**
	 * @param string $message
	 */
	public function __construct($message) {
		parent::__construct($message, 0);
	}
}
