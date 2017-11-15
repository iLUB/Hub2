<?php

use SRAG\Plugins\Hub2\Exception\HubException;
use SRAG\Plugins\Hub2\Origin\AROrigin;
use SRAG\Plugins\Hub2\Config\HubConfig;
use SRAG\Plugins\Hub2\Origin\OriginImplementationTemplateGenerator;
use SRAG\Plugins\Hub2\Origin\OriginRepository;
use SRAG\Plugins\Hub2\Sync\OriginSyncFactory;
use SRAG\Plugins\Hub2\Sync\Summary\OriginSyncSummaryFactory;
use SRAG\Plugins\Hub2\UI\OriginConfigFormGUI;

/**
 * Class hub2ConfigOriginsGUI
 *
 * @author            Stefan Wanzenried <sw@studer-raimann.ch>
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_calls      hub2ConfigOriginsGUI: hub2DataGUI
 */
class hub2ConfigOriginsGUI extends hub2MainGUI {

	const CMD_DELETE = 'delete';
	const CMD_INDEX = 'index';
	const ORIGIN_ID = 'origin_id';
	const SUBTAB_DATA = 'subtab_data';
	const SUBTAB_ORIGINS = 'subtab_origins';
	/**
	 * @var \SRAG\Plugins\Hub2\Sync\Summary\OriginSyncSummaryFactory
	 */
	protected $summaryFactory;
	/**
	 * @var ilHub2Plugin
	 */
	protected $pl;
	/**
	 * @var \SRAG\Plugins\Hub2\Origin\OriginFactory
	 */
	protected $originFactory;
	/**
	 * @var HubConfig
	 */
	protected $hubConfig;
	/**
	 * @var OriginRepository
	 */
	protected $originRepository;
	use \SRAG\Plugins\Hub2\Helper\DIC;


	public function __construct() {
		parent::__construct();
		$this->originFactory = new \SRAG\Plugins\Hub2\Origin\OriginFactory($this->db());
		$this->hubConfig = new HubConfig();
		$this->originRepository = new OriginRepository();
		$this->summaryFactory = new OriginSyncSummaryFactory();
	}


	public function executeCommand() {
		$this->checkAccess();
		parent::executeCommand();
		switch ($this->ctrl()->getNextClass()) {
			case strtolower(hub2DataGUI::class):
				$this->ctrl()->forwardCommand(new hub2DataGUI());
				break;
		}
	}


	protected function initTabs() {
		$this->tabs()
		     ->addSubTab(self::SUBTAB_ORIGINS, $this->pl->txt(self::SUBTAB_ORIGINS), $this->ctrl()
		                                                                                  ->getLinkTarget($this, self::CMD_INDEX));
		$this->tabs()->addSubTab(self::SUBTAB_DATA, $this->pl->txt(self::SUBTAB_DATA), $this->ctrl()
		                                                                                    ->getLinkTargetByClass(hub2DataGUI::class, hub2DataGUI::CMD_INDEX));

		$this->tabs()->activateTab(self::TAB_ORIGINS);
		$this->tabs()->activateSubTab(self::SUBTAB_ORIGINS);
	}


	protected function index() {
		$button = ilLinkButton::getInstance();
		$button->setCaption($this->pl->txt('origin_table_button_add'), false);
		$button->setUrl($this->ctrl()->getLinkTarget($this, 'addOrigin'));
		$this->toolbar()->addButtonInstance($button);
		$table = new \SRAG\Plugins\Hub2\UI\OriginsTableGUI($this, self::CMD_INDEX, new OriginRepository());
		$this->tpl()->setContent($table->getHTML());
	}


	protected function cancel() {
		$this->index();
	}


	protected function addOrigin() {
		$form = new OriginConfigFormGUI($this, $this->hubConfig, new OriginRepository(), new \SRAG\Plugins\Hub2\Origin\User\ARUserOrigin());
		$this->tpl()->setContent($form->getHTML());
	}


	protected function createOrigin() {
		$form = new OriginConfigFormGUI($this, $this->hubConfig, new OriginRepository(), new \SRAG\Plugins\Hub2\Origin\User\ARUserOrigin());
		if ($form->checkInput()) {
			$origin = $this->originFactory->createByType($form->getInput('object_type'));
			$origin->setTitle($form->getInput('title'));
			$origin->setDescription($form->getInput('description'));
			$origin->save();
			ilUtil::sendSuccess($this->pl->txt('msg_success_create_origin'), true);
			$this->ctrl()->setParameter($this, self::ORIGIN_ID, $origin->getId());
			$this->ctrl()->redirect($this, 'editOrigin');
		}
		$form->setValuesByPost();
		$this->tpl()->setContent($form->getHTML());
	}


	protected function saveOrigin() {
		/** @var AROrigin $origin */
		$origin = $this->getOrigin((int)$_POST[self::ORIGIN_ID]);
		$this->tpl()->setTitle($origin->getTitle());
		$form = $this->getForm($origin);
		if ($form->checkInput()) {
			$origin->setTitle($form->getInput('title'));
			$origin->setDescription($form->getInput('description'));
			$origin->setActive($form->getInput('active'));
			$origin->setImplementationClassName($form->getInput('implementation_class_name'));
			// Get the config data as an array
			$configData = [];
			$propertyData = [];
			foreach ($form->getInputItemsRecursive() as $item) {
				if (strpos($item->getPostVar(), 'config_') === 0) {
					$key = substr($item->getPostVar(), 7);
					$configData[$key] = $form->getInput($item->getPostVar());
				} else {
					if (strpos($item->getPostVar(), 'prop_') === 0) {
						$key = substr($item->getPostVar(), 5);
						$propertyData[$key] = $form->getInput($item->getPostVar());
					}
				}
			}
			$origin->config()->setData($configData);
			$origin->properties()->setData($propertyData);
			$origin->save();
			ilUtil::sendSuccess($this->pl->txt('msg_origin_saved'), true);
			// Try to create the implementation class file automatically
			$generator = new OriginImplementationTemplateGenerator($this->hubConfig);
			try {
				$result = $generator->create($origin);
				if ($result) {
					ilUtil::sendInfo("Created class implementation file: "
					                 . $generator->getClassFilePath($origin), true);
				}
			} catch (HubException $e) {
				$msg = 'Unable to create class implementation file, you must create it manually at: '
				       . $generator->getClassFilePath($origin);
				ilUtil::sendInfo($msg, true);
			}
			$this->ctrl()->saveParameter($this, self::ORIGIN_ID);
			$this->ctrl()->redirect($this, 'editOrigin');
		}
		$form->setValuesByPost();
		$this->tpl()->setContent($form->getHTML());
	}


	protected function editOrigin() {
		$origin = $this->getOrigin((int)$_GET[self::ORIGIN_ID]);
		$this->tpl()->setTitle($origin->getTitle());
		$form = $this->getForm($origin);
		$this->tpl()->setContent($form->getHTML());
	}


	protected function activateAll() {
		foreach ($this->originRepository->all() as $repository) {
			$repository->setActive(true);
			$repository->save();
		}
		ilUtil::sendSuccess($this->pl->txt('msg_origin_activated'), true);
		$this->ctrl()->redirect($this);
	}


	protected function deactivateAll() {
		foreach ($this->originRepository->all() as $repository) {
			$repository->setActive(false);
			$repository->save();
		}
		ilUtil::sendSuccess($this->pl->txt('msg_origin_deactivated'), true);
		$this->ctrl()->redirect($this);
	}


	/**
	 *
	 */
	protected function run() {
		$summary = $this->summaryFactory->web();
		foreach ($this->originFactory->getAllActive() as $origin) {
			$originSyncFactory = new OriginSyncFactory($origin);
			$originSync = $originSyncFactory->instance();
			try {
				$originSync->execute();
			} catch (\Exception $e) {
				// Any exception being forwarded to here means that we failed to execute the sync at some point
				ilUtil::sendFailure("{$e->getMessage()} <pre>{$e->getTraceAsString()}</pre>", true);
			}
			$OriginLog = new \SRAG\Plugins\Hub2\Log\OriginLog($originSync->getOrigin());
			$OriginLog->write($summary->getSummaryOfOrigin($originSync));

			$summary->addOriginSync($originSync);
		}
		ilUtil::sendInfo(nl2br($summary->getOutputAsString()), true);
		$this->ctrl()->redirect($this);
	}


	protected function runOriginSync() {
		$origin = $this->getOrigin((int)$_GET[self::ORIGIN_ID]);
		$summary = $this->summaryFactory->web();
		$originSyncFactory = new OriginSyncFactory($origin);
		$originSync = $originSyncFactory->instance();
		try {
			$originSync->execute();
		} catch (\Exception $e) {
			// Any exception being forwarded to here means that we failed to execute the sync at some point
			ilUtil::sendFailure("{$e->getMessage()} <pre>{$e->getTraceAsString()}</pre>", true);
		}
		$summary->addOriginSync($originSync);
		ilUtil::sendInfo(nl2br($summary->getOutputAsString()), true);
		//		$this->ctrl()->redirect($this);
	}


	protected function confirmDelete() {
		$f = new \SRAG\Plugins\Hub2\Origin\OriginFactory($this->db());
		$o = $f->getById($this->http()->request()->getQueryParams()[self::ORIGIN_ID]);

		$c = new ilConfirmationGUI();
		$c->setFormAction($this->ctrl()->getFormAction($this));
		$c->addItem(self::ORIGIN_ID, $o->getId(), $o->getTitle());
		$c->setConfirm($this->lng()->txt('confirm_delete_button'), self::CMD_DELETE);
		$c->setCancel($this->lng()->txt('cancel_delete_button'), self::CMD_INDEX);

		$this->tpl()->setContent($c->getHTML());
	}


	protected function delete() {
		$f = new \SRAG\Plugins\Hub2\Origin\OriginFactory($this->db());
		$o = $f->getById($this->http()->request()->getParsedBody()[self::ORIGIN_ID]);
		$o->delete();
		$this->ctrl()->redirect($this, self::CMD_INDEX);
	}


	/**
	 * Check access based on plugin configuration.
	 * Returns to personal desktop if a user does not have permission to administrate hub.
	 */
	protected function checkAccess() {
		$roles = array_unique(array_merge($this->hubConfig->getAdministrationRoleIds(), [ 2 ]));
		if (!$this->rbac()->review()->isAssignedToAtLeastOneGivenRole($this->user()
		                                                                   ->getId(), $roles)) {
			ilUtil::sendFailure($this->language()->txt('permission_denied'), true);
			$this->ctrl()->redirectByClass('ilpersonaldesktopgui');
		}
	}


	/**
	 * @param AROrigin $origin
	 *
	 * @return OriginConfigFormGUI
	 */
	protected function getForm(AROrigin $origin) {
		$formFactory = new \SRAG\Plugins\Hub2\UI\OriginFormFactory();
		$formClass = $formFactory->getFormClassNameByOrigin($origin);
		$form = new $formClass($this, $this->hubConfig, new OriginRepository(), $origin);

		return $form;
	}


	/**
	 * @param int $id
	 *
	 * @return AROrigin
	 * @throws ilException
	 */
	protected function getOrigin($id) {
		/** @var AROrigin $origin */
		$origin = $this->originFactory->getById((int)$id);
		if ($origin === null) {
			throw new \ilException(sprintf("Origin with ID '%s' not found.", $id));
		}

		return $origin;
	}
}