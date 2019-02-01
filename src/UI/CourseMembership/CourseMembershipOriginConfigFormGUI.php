<?php

namespace srag\Plugins\Hub2\UI\CourseMembership;

use ilCheckboxInputGUI;
use ilRadioGroupInputGUI;
use ilRadioOption;
use srag\Plugins\Hub2\Origin\CourseMembership\ARCourseMembershipOrigin;
use srag\Plugins\Hub2\Origin\Properties\CourseMembership\CourseMembershipProperties;
use srag\Plugins\Hub2\UI\OriginConfig\OriginConfigFormGUI;

/**
 * Class CourseMembershipOriginConfigFormGUI
 *
 * @package srag\Plugins\Hub2\UI\CourseMembership
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class CourseMembershipOriginConfigFormGUI extends OriginConfigFormGUI {

	/**
	 * @var ARCourseMembershipOrigin
	 */
	protected $origin;


	/**
	 * @inheritdoc
	 */
	protected function addSyncConfig() {
		parent::addSyncConfig();
		$item = $this->getItemByPostVar(self::POST_VAR_ADHOC);

		$subitem = new ilCheckboxInputGUI(self::plugin()->translate("origin_form_field_adhoc_parent_scope"), "adhoc_parent_scope");
		$subitem->setChecked($this->origin->isAdhocParentScope());
		$subitem->setInfo(self::plugin()->translate("origin_form_field_adhoc_parent_scope_info"));
		$item->addSubItem($subitem);
	}


	/**
	 * @inheritdoc
	 */
	protected function addPropertiesNew() {
		parent::addPropertiesNew();
	}


	/**
	 * @inheritdoc
	 */
	protected function addPropertiesUpdate() {
		parent::addPropertiesUpdate();
	}


	/**
	 * @inheritdoc
	 */
	protected function addPropertiesDelete() {
		parent::addPropertiesDelete();

		$delete = new ilRadioGroupInputGUI(self::plugin()->translate('crs_prop_delete_mode'), $this->prop(CourseMembershipProperties::DELETE_MODE));
		$delete->setValue($this->origin->properties()->get(CourseMembershipProperties::DELETE_MODE));

		$opt = new ilRadioOption(self::plugin()->translate('crs_prop_delete_mode_none'), CourseMembershipProperties::DELETE_MODE_NONE);
		$delete->addOption($opt);
		$opt = new ilRadioOption(self::plugin()->translate('crs_membership_prop_delete_mode_delete'), CourseMembershipProperties::DELETE_MODE_DELETE);
		$delete->addOption($opt);
		$this->addItem($delete);
	}
}
