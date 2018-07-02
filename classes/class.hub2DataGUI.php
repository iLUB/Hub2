<?php

use SRAG\Plugins\Hub2\Helper\DIC;
use SRAG\Plugins\Hub2\Object\ObjectFactory;
use SRAG\Plugins\Hub2\Origin\OriginFactory;
use SRAG\Plugins\Hub2\UI\DataTableGUI;

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Class hub2DataGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class hub2DataGUI extends hub2MainGUI {

	use DIC;
	const CMD_INDEX = 'index';


	public function executeCommand() {
		$this->initTabs();
		$cmd = $this->ctrl()->getCmd(self::CMD_INDEX);
		$this->{$cmd}();
	}


	protected function index() {
		$table = new DataTableGUI($this, self::CMD_INDEX);
		$this->tpl()->setContent($table->getHTML());
	}


	protected function applyFilter() {
		$table = new DataTableGUI($this, self::CMD_INDEX);
		$table->writeFilterToSession();
		$table->resetOffset();
		$this->ctrl()->redirect($this, self::CMD_INDEX);
	}


	protected function resetFilter() {
		$table = new DataTableGUI($this, self::CMD_INDEX);
		$table->resetFilter();
		$table->resetOffset();
		$this->ctrl()->redirect($this, self::CMD_INDEX);
	}


	protected function initTabs() {
		$this->tabs()->activateSubTab(hub2ConfigOriginsGUI::SUBTAB_DATA);
	}


	protected function renderData() {
		$ext_id = $this->http()->request()->getQueryParams()[DataTableGUI::F_EXT_ID];
		$origin_id = $this->http()->request()->getQueryParams()[DataTableGUI::F_ORIGIN_ID];

		$origin_factory = new OriginFactory($this->db());
		$object_factory = new ObjectFactory($origin_factory->getById($origin_id));

		$object = $object_factory->undefined($ext_id);

		$factory = $this->ui()->factory();

		$properties = array_merge([
			"period" => $object->getPeriod(),
			"delivery_date" => $object->getDeliveryDate()->format(DATE_ATOM),
			"processed_date" => $object->getProcessedDate()->format(DATE_ATOM),
			"ilias_id" => $object->getILIASId(),
			"status" => $object->getStatus(),
		], $object->getData());

		if ($object instanceof \SRAG\Plugins\Hub2\Object\IMetadataAwareObject) {
			foreach ($object->getMetaData() as $metadata) {
				$properties[sprintf($this->pl->txt("table_md"), $metadata->getIdentifier())] = $metadata->getValue();
			}
		}

		if ($object instanceof \SRAG\Plugins\Hub2\Object\ITaxonomyAwareObject) {
			foreach ($object->getTaxonomies() as $taxonomy) {
				$properties[sprintf($this->pl->txt("table_tax"), $taxonomy->getTitle())] = implode(", ", $taxonomy->getNodeTitlesAsArray());
			}
		}

		$filtered = [];
		foreach ($properties as $key => $property) {
			if (!is_null($property)) {
				$filtered[$key] = (string)$property;
			}
			if ($property === '') {
				$filtered[$key] = "&nbsp;";
			}
		}

		ksort($filtered);

		// Unfortunately the item suchs in rendering in Modals, therefore we take a descriptive listing
		$data_table = $factory->item()->standard(sprintf($this->pl->txt("table_ext_id"), $object->getExtId()))->withProperties($filtered);

		$data_table = $factory->listing()->descriptive($filtered);

		$renderer = $this->ui()->renderer();

		$modal = $factory->modal()->roundtrip(sprintf($this->pl->txt("table_hash"), $object->getHashCode()), $data_table);

		echo $renderer->renderAsync($modal);
		exit;
	}
}
