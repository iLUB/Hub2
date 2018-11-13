<?php

namespace srag\Plugins\Hub2\UI;

use ilHub2Plugin;
use srag\DIC\Hub2\DICTrait;

/**
 * Class AbstractGUI
 *
 * @package srag\Plugins\Hub2\UI
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @deprecated
 */
abstract class AbstractGUI {

	use DICTrait;
	const PLUGIN_CLASS_NAME = ilHub2Plugin::class;


	public function __construct() {

	}
}
