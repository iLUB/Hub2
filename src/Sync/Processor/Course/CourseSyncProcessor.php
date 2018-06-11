<?php

namespace SRAG\Plugins\Hub2\Sync\Processor\Course;

use SRAG\Plugins\Hub2\Exception\HubException;
use SRAG\Plugins\Hub2\Log\ILog;
use SRAG\Plugins\Hub2\Notification\OriginNotifications;
use SRAG\Plugins\Hub2\Object\Course\CourseDTO;
use SRAG\Plugins\Hub2\Object\DTO\IDataTransferObject;
use SRAG\Plugins\Hub2\Object\ObjectFactory;
use SRAG\Plugins\Hub2\Origin\Config\CourseOriginConfig;
use SRAG\Plugins\Hub2\Origin\IOrigin;
use SRAG\Plugins\Hub2\Origin\IOriginImplementation;
use SRAG\Plugins\Hub2\Origin\OriginRepository;
use SRAG\Plugins\Hub2\Origin\Properties\CourseOriginProperties;
use SRAG\Plugins\Hub2\Sync\IObjectStatusTransition;
use SRAG\Plugins\Hub2\Sync\Processor\MetadataSyncProcessor;
use SRAG\Plugins\Hub2\Sync\Processor\ObjectSyncProcessor;
use SRAG\Plugins\Hub2\Sync\Processor\TaxonomySyncProcessor;

/**
 * Class CourseSyncProcessor
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\Plugins\Hub2\Sync\Processor
 */
class CourseSyncProcessor extends ObjectSyncProcessor implements ICourseSyncProcessor {

	use TaxonomySyncProcessor;
	use MetadataSyncProcessor;
	/**
	 * @var CourseOriginProperties
	 */
	protected $props;
	/**
	 * @var CourseOriginConfig
	 */
	protected $config;
	/**
	 * @var ICourseActivities
	 */
	protected $courseActivities;
	/**
	 * @var array
	 */
	protected static $properties = [
		'title',
		'description',
		'importantInformation',
		'contactResponsibility',
		'contactEmail',
		'owner',
		'subscriptionLimitationType',
		'viewMode',
		'contactName',
		'syllabus',
		'contactConsultation',
		'contactPhone',
		'activationType',
	];


	/**
	 * @param IOrigin                 $origin
	 * @param IOriginImplementation   $implementation
	 * @param IObjectStatusTransition $transition
	 * @param ILog                    $originLog
	 * @param OriginNotifications     $originNotifications
	 * @param ICourseActivities       $courseActivities
	 */
	public function __construct(IOrigin $origin, IOriginImplementation $implementation, IObjectStatusTransition $transition, ILog $originLog, OriginNotifications $originNotifications, ICourseActivities $courseActivities) {
		parent::__construct($origin, $implementation, $transition, $originLog, $originNotifications);
		$this->props = $origin->properties();
		$this->config = $origin->config();
		$this->courseActivities = $courseActivities;
	}


	/**
	 * @return array
	 */
	public static function getProperties() {
		return self::$properties;
	}


	/**
	 * @inheritdoc
	 */
	protected function handleCreate(IDataTransferObject $dto) {
		/** @var CourseDTO $dto */
		$ilObjCourse = new \ilObjCourse();
		$ilObjCourse->setImportId($this->getImportId($dto));
		// Find the refId under which this course should be created
		$parentRefId = $this->determineParentRefId($dto);
		// Check if we should create some dependence categories
		$parentRefId = $this->buildDependenceCategories($dto, $parentRefId);
		$ilObjCourse->create();
		$ilObjCourse->createReference();
		$ilObjCourse->putInTree($parentRefId);
		$ilObjCourse->setPermissions($parentRefId);
		// Pass properties from DTO to ilObjUser
		foreach (self::getProperties() as $property) {
			$setter = "set" . ucfirst($property);
			$getter = "get" . ucfirst($property);
			if ($dto->$getter() !== null) {
				$ilObjCourse->$setter($dto->$getter());
			}
		}
		if ($this->props->get(CourseOriginProperties::SET_ONLINE)) {
			$ilObjCourse->setOfflineStatus(false);
			$ilObjCourse->setActivationType(IL_CRS_ACTIVATION_UNLIMITED);
		}
		if ($this->props->get(CourseOriginProperties::CREATE_ICON)) {
			// TODO
			//			$this->updateIcon($this->ilias_object);
			//			$this->ilias_object->update();
		}
		if ($this->props->get(CourseOriginProperties::SEND_CREATE_NOTIFICATION)) {
			// TODO
		}
		$ilObjCourse->update();

		return $ilObjCourse;
	}


	/**
	 * @inheritdoc
	 */
	protected function handleUpdate(IDataTransferObject $dto, $ilias_id) {
		/** @var CourseDTO $dto */
		$ilObjCourse = $this->findILIASCourse($ilias_id);
		if ($ilObjCourse === null) {
			return null;
		}
		// Update some properties if they should be updated depending on the origin config
		foreach (self::getProperties() as $property) {
			if (!$this->props->updateDTOProperty($property)) {
				continue;
			}
			$setter = "set" . ucfirst($property);
			$getter = "get" . ucfirst($property);
			if ($dto->$getter() !== null) {
				$ilObjCourse->$setter($dto->$getter());
			}
		}
		if ($this->props->get(CourseOriginProperties::SET_ONLINE_AGAIN)) {
			$ilObjCourse->setOfflineStatus(false);
			$ilObjCourse->setActivationType(IL_CRS_ACTIVATION_UNLIMITED);
		}
		if ($this->props->get(CourseOriginProperties::MOVE_COURSE)) {
			$this->moveCourse($ilObjCourse, $dto);
		}
		$ilObjCourse->update();

		return $ilObjCourse;
	}


	/**
	 * @inheritdoc
	 */
	protected function handleDelete($ilias_id) {
		$ilObjCourse = $this->findILIASCourse($ilias_id);
		if ($ilObjCourse === null) {
			return null;
		}
		if ($this->props->get(CourseOriginProperties::DELETE_MODE)
		    == CourseOriginProperties::DELETE_MODE_NONE) {
			return $ilObjCourse;
		}
		global $DIC;
		$tree = $DIC->repositoryTree();
		switch ($this->props->get(CourseOriginProperties::DELETE_MODE)) {
			case CourseOriginProperties::DELETE_MODE_OFFLINE:
				$ilObjCourse->setOfflineStatus(true);
				$ilObjCourse->update();
				break;
			case CourseOriginProperties::DELETE_MODE_DELETE:
				$ilObjCourse->delete();
				break;
			case CourseOriginProperties::DELETE_MODE_MOVE_TO_TRASH:
				$tree->moveToTrash($ilObjCourse->getRefId(), true);
				break;
			case CourseOriginProperties::DELETE_MODE_DELETE_OR_OFFLINE:
				if ($this->courseActivities->hasActivities($ilObjCourse)) {
					$ilObjCourse->setOfflineStatus(true);
					$ilObjCourse->update();
				} else {
					$tree->moveToTrash($ilObjCourse->getRefId(), true);
				}
				break;
		}

		return $ilObjCourse;
	}


	/**
	 * @param CourseDTO $course
	 *
	 * @return int
	 * @throws HubException
	 */
	protected function determineParentRefId(CourseDTO $course) {
		global $DIC;
		$tree = $DIC->repositoryTree();
		if ($course->getParentIdType() == CourseDTO::PARENT_ID_TYPE_REF_ID) {
			if ($tree->isInTree($course->getParentId())) {
				return $course->getParentId();
			}
			// The ref-ID does not exist in the tree, use the fallback parent ref-ID according to the config
			$parentRefId = $this->config->getParentRefIdIfNoParentIdFound();
			if (!$tree->isInTree($parentRefId)) {
				throw new HubException("Could not find the fallback parent ref-ID in tree: '{$parentRefId}'");
			}

			return $parentRefId;
		}
		if ($course->getParentIdType() == CourseDTO::PARENT_ID_TYPE_EXTERNAL_EXT_ID) {
			// The stored parent-ID is an external-ID from a category.
			// We must search the parent ref-ID from a category object synced by a linked origin.
			// --> Get an instance of the linked origin and lookup the category by the given external ID.
			$linkedOriginId = $this->config->getLinkedOriginId();
			if (!$linkedOriginId) {
				throw new HubException("Unable to lookup external parent ref-ID because there is no origin linked");
			}
			$originRepository = new OriginRepository();
			$origin = array_pop(array_filter($originRepository->categories(), function ($origin) use ($linkedOriginId) {
				/** @var $origin IOrigin */
				return $origin->getId() == $linkedOriginId;
			}));
			if ($origin === null) {
				$msg = "The linked origin syncing categories was not found, please check that the correct origin is linked";
				throw new HubException($msg);
			}
			$objectFactory = new ObjectFactory($origin);
			$category = $objectFactory->category($course->getParentId());
			if (!$category->getILIASId()) {
				throw new HubException("The linked category does not (yet) exist in ILIAS");
			}
			if (!$tree->isInTree($category->getILIASId())) {
				throw new HubException("Could not find the ref-ID of the parent category in the tree: '{$category->getILIASId()}'");
			}

			return $category->getILIASId();
		}

		return 0;
	}


	/**
	 * @param CourseDTO $object
	 * @param           $parentRefId
	 *
	 * @return int
	 */
	protected function buildDependenceCategories(CourseDTO $object, $parentRefId) {
		if ($object->getFirstDependenceCategory() !== null) {
			$parentRefId = $this->buildDependenceCategory($object->getFirstDependenceCategory(), $parentRefId, 1);
		}
		if ($object->getFirstDependenceCategory() !== null
		    && $object->getSecondDependenceCategory() !== null) {
			$parentRefId = $this->buildDependenceCategory($object->getSecondDependenceCategory(), $parentRefId, 2);
		}
		if ($object->getFirstDependenceCategory() !== null
		    && $object->getSecondDependenceCategory() !== null
		    && $object->getThirdDependenceCategory() !== null) {
			$parentRefId = $this->buildDependenceCategory($object->getThirdDependenceCategory(), $parentRefId, 3);
		}

		return $parentRefId;
	}


	/**
	 * Creates a category under the given $parentRefId if it does not yet exist.
	 * Note that this implementation is copied over from the old hub plugin: We check if we
	 * find a category having the same title. If not, a new category is created.
	 * It would be better to identify the category over the unique import ID and then update
	 * the title of the category, if necessary.
	 *
	 * @param string $title
	 * @param int    $parentRefId
	 * @param int    $level
	 *
	 * @return int
	 */
	protected function buildDependenceCategory($title, $parentRefId, $level) {
		global $DIC;
		static $cache = [];
		// We use a cache for created dependence categories to save some SQL queries
		$cacheKey = md5($title . $parentRefId . $level);
		if (isset($cache[$cacheKey])) {
			return $cache[$cacheKey];
		}
		$categories = $DIC->repositoryTree()->getChildsByType($parentRefId, 'cat');
		$matches = array_filter($categories, function ($category) use ($title) {
			return $category['title'] == $title;
		});
		if (count($matches) > 0) {
			$category = array_pop($matches);
			$cache[$cacheKey] = $category['ref_id'];

			return $category['ref_id'];
		}
		// No category with the given title found, create it!
		$importId = implode('_', [
			'srhub',
			$this->origin->getId(),
			$parentRefId,
			'depth',
			$level,
		]);
		$ilObjCategory = new \ilObjCategory();
		$ilObjCategory->setTitle($title);
		$ilObjCategory->setImportId($importId);
		$ilObjCategory->create();
		$ilObjCategory->createReference();
		$ilObjCategory->putInTree($parentRefId);
		$ilObjCategory->setPermissions($parentRefId);
		$cache[$cacheKey] = $ilObjCategory->getRefId();

		return $ilObjCategory->getRefId();
	}


	/**
	 * @param int $iliasId
	 *
	 * @return \ilObjCourse|null
	 */
	protected function findILIASCourse($iliasId) {
		if (!\ilObjCourse::_exists($iliasId, true)) {
			return null;
		}

		return new \ilObjCourse($iliasId);
	}


	/**
	 * Move the course to a new parent.
	 * Note: May also create the dependence categories
	 *
	 * @param           $ilObjCourse
	 * @param CourseDTO $course
	 */
	protected function moveCourse(\ilObjCourse $ilObjCourse, CourseDTO $course) {
		global $DIC;
		$parentRefId = $this->determineParentRefId($course);
		$parentRefId = $this->buildDependenceCategories($course, $parentRefId);
		if ($DIC->repositoryTree()->isDeleted($ilObjCourse->getRefId())) {
			$ilRepUtil = new \ilRepUtil();
			$ilRepUtil->restoreObjects($parentRefId, [ $ilObjCourse->getRefId() ]);
		}
		$oldParentRefId = $DIC->repositoryTree()->getParentId($ilObjCourse->getRefId());
		if ($oldParentRefId == $parentRefId) {
			return;
		}
		$DIC->repositoryTree()->moveTree($ilObjCourse->getRefId(), $parentRefId);
		$DIC->rbac()
		    ->admin()
		    ->adjustMovedObjectPermissions($ilObjCourse->getRefId(), $oldParentRefId);
	}
}