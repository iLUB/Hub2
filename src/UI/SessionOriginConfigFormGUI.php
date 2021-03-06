<?php namespace SRAG\Plugins\Hub2\UI;

use SRAG\Plugins\Hub2\Origin\Properties\SessionOriginProperties;
use SRAG\Plugins\Hub2\Origin\Session\ARSessionOrigin;

/**
 * Class SessionOriginConfigFormGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class SessionOriginConfigFormGUI extends OriginConfigFormGUI {

	/**
	 * @var ARSessionOrigin
	 */
	protected $origin;


	protected function addSyncConfig() {
		parent::addSyncConfig();
	}


	protected function addPropertiesNew() {
		parent::addPropertiesNew();
	}


	protected function addPropertiesUpdate() {
		$cb = new \ilCheckboxInputGUI($this->pl->txt('sess_prop_move'), $this->prop(SessionOriginProperties::MOVE_SESSION));
		$cb->setInfo($this->pl->txt('sess_prop_move_info'));
		$this->addItem($cb);
		
		parent::addPropertiesUpdate();
	}


	protected function addPropertiesDelete() {
		$delete = new \ilRadioGroupInputGUI($this->pl->txt('sess_prop_delete_mode'), $this->prop(SessionOriginProperties::DELETE_MODE));
		$delete->setValue($this->origin->properties()->get(SessionOriginProperties::DELETE_MODE));

		$opt = new \ilRadioOption($this->pl->txt('sess_prop_delete_mode_none'), SessionOriginProperties::DELETE_MODE_NONE);
		$delete->addOption($opt);

		$opt = new \ilRadioOption($this->pl->txt('sess_prop_delete_mode_delete'), SessionOriginProperties::DELETE_MODE_DELETE);
		$delete->addOption($opt);

		$opt = new \ilRadioOption($this->pl->txt('sess_prop_delete_mode_trash'), SessionOriginProperties::DELETE_MODE_MOVE_TO_TRASH);
		$delete->addOption($opt);

		$this->addItem($delete);
		parent::addPropertiesDelete();
	}
}