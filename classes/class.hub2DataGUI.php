<?php

use SRAG\Plugins\Hub2\Object\ObjectFactory;
use SRAG\Plugins\Hub2\Origin\OriginFactory;
use SRAG\Plugins\Hub2\UI\DataTableGUI;

require_once(__DIR__ . '/class.ilHub2Plugin.php');

/**
 * Class hub2DataGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class hub2DataGUI extends hub2MainGUI {

	use \SRAG\Plugins\Hub2\Helper\DIC;
	const CMD_INDEX = 'index';


	protected function index() {
		$table = new DataTableGUI($this, self::CMD_INDEX);

		$this->tpl()->setContent($table->getHTML());
	}


	protected function initTabs() {
		$this->tabs()->activateSubTab(hub2ConfigOriginsGUI::SUBTAB_DATA);
	}


	protected function renderData() {
		$ext_id = $this->http()->request()->getQueryParams()[DataTableGUI::F_EXT_ID];
		$origin_id = $this->http()->request()->getQueryParams()[DataTableGUI::F_ORIGIN_ID];
		$is_async = ($this->http()->request()->getQueryParams()["cmdMode"] == "async");

		$origin_factory = new OriginFactory($this->db());
		$object_factory = new ObjectFactory($origin_factory->getById($origin_id));

		$object = $object_factory->undefined($ext_id);

		$factory = $this->ui()->factory();

		$properties = array_merge([
			"period"         => $object->getPeriod(),
			"delivery_date"  => $object->getDeliveryDate()->format(DATE_ATOM),
			"processed_date" => $object->getProcessedDate()->format(DATE_ATOM),
			"ilias_id"       => $object->getILIASId(),
			"status"         => $object->getStatus(),
		], $object->getData());
		$filtered = [];
		foreach ($properties as $key => $property) {
			if (!is_null($property)) {
				$filtered[$key] = (string)$property;
			}
		}

		ksort($filtered);

		// Unfortunately the item suchs in rendering in Modals, therefore we take a descriptive listing
		$data_table = $factory->item()
		                      ->standard("Ext-ID: " . $object->getExtId())
		                      ->withProperties($filtered);

		$data_table = $factory->listing()->descriptive($filtered);

		$renderer = $this->ui()->renderer();

		$modal = $factory->modal()->roundtrip("Hash: " . $object->getHashCode(), $data_table);

		echo $renderer->renderAsync($modal);
		exit;

		$button = $factory->button()->standard('Open', '')->withOnClick($modal->getShowSignal());
		$this->tpl()->setContent($renderer->render([ $button, $modal ]));
	}
}
