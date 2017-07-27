<?php namespace SRAG\Hub2\Origin;

/**
 * Class OriginRepository
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\Hub2\Origin
 */
class OriginRepository implements IOriginRepository {


	/**
	 * @inheritdoc
	 */
	public function all() {
		return array_merge(
			$this->users(),
			$this->categories(),
			$this->courses(),
			$this->courseMemberships(),
			$this->groups(),
			$this->groupMemberships(),
			$this->sessions()
		);
	}

	/**
	 * @inheritdoc
	 */
	public function allActive() {
		return array_filter($this->all(), function($origin) {
			/** @var $origin IOrigin */
			return $origin->isActive();
		});
	}

	/**
	 * @inheritdoc
	 */
	public function users() {
		return ARUserOrigin::where(['object_type' => IOrigin::OBJECT_TYPE_USER])->get();
	}

	/**
	 * @inheritdoc
	 */
	public function courses() {
		return ARCourseOrigin::where(['object_type' => IOrigin::OBJECT_TYPE_COURSE])->get();
	}

	/**
	 * @inheritdoc
	 */
	public function categories() {
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function courseMemberships() {
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function groups() {
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function groupMemberships() {
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function sessions() {
		return [];
	}
}