<?php

namespace SRAG\Plugins\Hub2\Taxonomy;

use SRAG\Plugins\Hub2\Taxonomy\Node\INode;

/**
 * Class Taxonomy
 *
 * @package SRAG\Plugins\Hub2\Taxonomy
 */
class Taxonomy implements ITaxonomy {

	/**
	 * @var INode[]
	 */
	protected $nodes = [];
	/**
	 * @var string
	 */
	protected $title = '';
	/**
	 * @var int
	 */
	protected $mode;


	/**
	 * Taxonomy constructor.
	 *
	 * @param string $title
	 * @param int    $mode
	 */
	public function __construct(string $title, int $mode) {
		$this->title = $title;
		$this->mode = $mode;
	}


	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}


	/**
	 * @inheritDoc
	 */
	public function getMode(): int {
		return $this->mode;
	}


	/**
	 * @inheritDoc
	 */
	public function getNodes(): array {
		return $this->nodes;
	}


	/**
	 * @inheritDoc
	 */
	public function attach(INode $node): ITaxonomy {
		$this->nodes[] = $node;

		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function __toString(): string {
		return "// TODO: Implement __toString() method.";
	}
}
