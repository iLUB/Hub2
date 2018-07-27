<?php namespace SRAG\Plugins\Hub2\Shortlink;

use SRAG\Plugins\Hub2\Object\ARObject;
use SRAG\Plugins\Hub2\Object\User\ARUser;

/**
 * Class NullLink
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractRepositoryLink extends AbstractBaseLink implements IObjectLink {

	/**
	 * @inheritDoc
	 */
	public function doesObjectExist(): bool {
		if (!$this->object->getILIASId()) {
			return false;
		}

		return \ilObject2::_exists($this->object->getILIASId(), true);
	}


	/**
	 * @inheritDoc
	 */
	public function isAccessGranted(): bool {
		global $DIC;

		return (bool)$DIC->access()->checkAccess("read", '', $this->object->getILIASId());
	}


	/**
	 * @inheritDoc
	 */
	public function getAccessGrantedInternalLink(): string {
		if ($this->isAccessGranted()) {
			return $this->getAccessGrantedExternalLink();
		} else {
			return $this->getAccessDeniedLink();
		}
	}


	/**
	 * @inheritDoc
	 */
	public function getAccessGrantedExternalLink(): string {
		$ref_id = $this->object->getILIASId();
		$link = $this->generateLink($ref_id);

		return $link;
	}


	/**
	 * @inheritDoc
	 */
	public function getAccessDeniedLink(): string {
		$ref_id = $this->findReadableParent();
		if ($ref_id === 0) {
			return "index.php";
		}

		$link = $this->generateLink($ref_id);

		return $link;
	}


	private function findReadableParent(): int {
		global $DIC;

		$ref_id = $this->object->getILIASId();

		while (!$DIC->access()->checkAccess('read', '', $ref_id) AND $ref_id != 1) {
			$ref_id = (int)$DIC->repositoryTree()->getParentId($ref_id);
		}

		if ($ref_id === 1) {
			if (!$DIC->access()->checkAccess('read', '', $ref_id)) {
				return 0;
			}
		}

		return (int)$ref_id;
	}


	/**
	 * @param $ref_id
	 *
	 * @return mixed|string
	 */
	private function generateLink($ref_id) {
		$link = \ilLink::_getLink($ref_id);
		$link = str_replace(ILIAS_HTTP_PATH, "", $link);

		return $link;
	}
}
