<?php

namespace SRAG\Plugins\Hub2\Taxonomy\Implementation;

use ilObjTaxonomy;
use ilTaxonomyTree;
use SRAG\Plugins\Hub2\Helper\DIC;
use SRAG\Plugins\Hub2\Taxonomy\ITaxonomy;
use SRAG\Plugins\Hub2\Taxonomy\Node\INode;

/**
 * Class AbstractTaxonomy
 *
 * @package SRAG\Plugins\Hub2\Taxonomy\Implementation
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractTaxonomy implements ITaxonomyImplementation {

	use DIC;
	/**
	 * @var int
	 */
	protected $tree_root_id;
	/**
	 * @var ilTaxonomyTree
	 */
	protected $tree;
	/**
	 * @var array
	 */
	protected $childs = [];
	/**
	 * @var ilObjTaxonomy
	 */
	protected $ilObjTaxonomy;
	/**
	 * @var ITaxonomy
	 */
	protected $taxonomy;
	/**
	 * @var int
	 */
	protected $ilias_parent_id;


	/**
	 * Taxonomy constructor.
	 *
	 * @param ITaxonomy $taxonomy
	 */
	public function __construct(ITaxonomy $taxonomy, int $ilias_parent_id) {
		$this->taxonomy = $taxonomy;
		$this->ilias_parent_id = $ilias_parent_id;
	}


	/**
	 * @return bool
	 */
	protected function taxonomyExists(): bool {
		$childsByType = $this->tree()->getChildsByType($this->getILIASParentId(), 'tax');
		if (!count($childsByType)) {
			return false;
		}
		foreach ($childsByType as $value) {
			if ($value["title"] === $this->getTaxonomy()->getTitle()) {
				$this->ilObjTaxonomy = new ilObjTaxonomy($value["obj_id"]);

				return true;
			}
		}

		return false;
	}


	/**
	 *
	 */
	protected function initTaxTree() {
		$this->tree = $this->ilObjTaxonomy->getTree();
		$this->tree_root_id = $this->tree->readRootId();
		$this->setChildrenByParentId($this->tree_root_id);
	}


	/**
	 * @param $parent_id
	 */
	protected function setChildrenByParentId($parent_id) {
		foreach ($this->tree->getChildsByTypeFilter($parent_id, array( "taxn" )) as $item) {
			$this->childs[$item['obj_id']] = $item['title'];
			$this->setChildrenByParentId($item['obj_id']);
		}
	}


	/**
	 * @param INode $node
	 *
	 * @return bool
	 */
	protected function nodeExists(INode $node): bool {
		return in_array($node->getTitle(), $this->childs);
	}


	/**
	 * @inheritDoc
	 */
	abstract public function write();


	/**
	 * @inheritDoc
	 */
	public function getTaxonomy(): ITaxonomy {
		return $this->taxonomy;
	}


	/**
	 * @inheritDoc
	 */
	public function getILIASParentId(): int {
		return $this->ilias_parent_id;
	}
}
