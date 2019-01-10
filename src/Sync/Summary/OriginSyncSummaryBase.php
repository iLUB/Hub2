<?php

namespace srag\Plugins\Hub2\Sync\Summary;

use ilHub2Plugin;
use ilMimeMail;
use srag\DIC\Hub2\DICTrait;
use srag\Plugins\Hub2\Object\IObject;
use srag\Plugins\Hub2\Sync\IOriginSync;
use srag\Plugins\Hub2\Utils\Hub2Trait;

/**
 * Class OriginSyncSummaryCron
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @package srag\Plugins\Hub2\Sync\Summary
 */
abstract class OriginSyncSummaryBase implements IOriginSyncSummary {

	use DICTrait;
	use Hub2Trait;
	const PLUGIN_CLASS_NAME = ilHub2Plugin::class;
	/**
	 * @var IOriginSync[]
	 */
	protected $syncs = array();


	/**
	 *
	 */
	public function __construct() {

	}


	/**
	 * @inheritdoc
	 */
	public function addOriginSync(IOriginSync $originSync) {
		$this->syncs[] = $originSync;
	}


	/**
	 * @inheritdoc
	 */
	public function getOutputAsString() {
		$return = "";
		foreach ($this->syncs as $sync) {
			$return .= $this->renderOneSync($sync) . "\n\n";
		}

		return $return;
	}


	/**
	 * @param IOriginSync $originSync
	 *
	 * @return string
	 */
	private function renderOneSync(IOriginSync $originSync) {
		// Print out some useful statistics: --> Should maybe be a OriginSyncSummary object
		$msg = self::plugin()->translate("summary_for", "", [ $originSync->getOrigin()->getTitle() ]) . "\n**********\n";
		$msg .= self::plugin()->translate("summary_delivered_data_sets", "", [ $originSync->getCountDelivered() ]) . "\n";
		$msg .= self::plugin()->translate("summary_failed", "", [ $originSync->getCountProcessedByStatus(IObject::STATUS_FAILED) ]) . "\n";
		$msg .= self::plugin()->translate("summary_created", "", [ $originSync->getCountProcessedByStatus(IObject::STATUS_CREATED) ]) . "\n";
		$msg .= self::plugin()->translate("summary_updated", "", [ $originSync->getCountProcessedByStatus(IObject::STATUS_UPDATED) ]) . "\n";
		$msg .= self::plugin()->translate("summary_outdated", "", [ $originSync->getCountProcessedByStatus(IObject::STATUS_OUTDATED) ]) . "\n";
		$msg .= self::plugin()->translate("summary_ignored", "", [ $originSync->getCountProcessedByStatus(IObject::STATUS_IGNORED) ]) . "\n";
		foreach ($originSync->getNotifications()->getMessages() as $context => $messages) {
			$msg .= "$context: \n**********\n";
			foreach ($messages as $message) {
				$msg .= "$message\n";
			}
			$msg .= "\n";
		}
		foreach ($originSync->getExceptions() as $exception) {
			$msg .= self::plugin()->translate("summary_exceptions") . "\n**********\n";
			$msg .= $exception->getMessage() . "\n\n";
		}
		$msg = rtrim($msg, "\n");

		return $msg;
	}


	/**
	 * @inheritdoc
	 */
	public function getSummaryOfOrigin(IOriginSync $originSync) {
		return $this->renderOneSync($originSync);
	}


	/**
	 * @inheritdoc
	 */
	public function sendNotifications() {
		$mail = new ilMimeMail();
		$mail->From(self::dic()->mailMimeSenderFactory()->system());

		foreach ($this->syncs as $originSync) {
			$summary_email = $originSync->getOrigin()->config()->getNotificationsSummary();
			$error_email = $originSync->getOrigin()->config()->getNotificationsErrors();
			$title = $originSync->getOrigin()->getTitle();
			if ($summary_email) {
				$mail->Subject(self::plugin()->translate("summary_notification", "", [ $title ]));
				$mail->To($summary_email);
				$mail->Body($this->renderOneSync($originSync));
				$mail->Send();
			}
			if ($error_email && $originSync->getExceptions()) {
				$mail->To($error_email);
				$mail->Subject(self::plugin()->translate("summary_exceptions_in", "", [ $title ]));
				$msg = self::plugin()->translate("summary_exceptions");
				foreach ($originSync->getExceptions() as $exception) {
					$msg .= "{$exception->getMessage()}\n";
					$msg .= self::plugin()->translate("summary_in", "", [ $exception->getFile() ]) . "\n";
				}
				$msg = rtrim($msg, "\n");

				$mail->Body($msg);
				$mail->Send();
			}
		}
	}
}
