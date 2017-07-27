<?php


namespace Rocket\Footer\JS;

class DOMCollection implements \Iterator {
	/**
	 * @var string
	 */
	private $tag_type;

	private $index = 0;

	/**
	 * @var \DOMNodeList
	 */
	private $list;
	/**
	 * @var \Rocket\Footer\JS\DOMDocument
	 */
	private $document;

	private $item_removed = false;

	/**
	 * DOMCollection constructor.
	 *
	 * @para string $tag_type
	 *
	 * @param \Rocket\Footer\JS\DOMDocument $document
	 * @param  string                       $tag_type
	 */
	public function __construct( $document, $tag_type ) {
		$this->tag_type = $tag_type;
		$this->document = $document;
		$this->fetch();
	}

	private function fetch() {
		$this->list = $this->document->getElementsByTagName( $this->tag_type );
	}

	/**
	 * Move forward to next element
	 *
	 * @link  http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function next() {
		if ( ! $this->item_removed ) {
			$this->index ++;
		}
		$this->item_removed = false;
	}

	/**
	 * Return the key of the current element
	 *
	 * @link  http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 * @since 5.0.0
	 */
	public function key() {
		return $this->index;
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link  http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 * @since 5.0.0
	 */
	public function valid() {
		return 0 < $this->list->length && null !== $this->list->item( $this->index );
	}

	public function remove() {
		$this->current()->remove();
		$this->index --;
		$this->item_removed = true;
		if ( 0 > $this->index ) {
			$this->rewind();
		}
	}

	/**
	 * Return the current element
	 *
	 * @link  http://php.net/manual/en/iterator.current.php
	 * @return \Rocket\Footer\JS\DOMElement
	 * @since 5.0.0
	 */
	public function current() {
		return $this->list->item( $this->index );
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link  http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function rewind() {
		$this->index = 0;
	}

	/**
	 * @param DOMElement $node
	 */
	public function add( $node ) {
		$this->document->appendChild( $node );
		$this->fetch();
	}

	/**
	 *
	 */
	public function flag_removed() {
		$this->item_removed = true;
	}
}