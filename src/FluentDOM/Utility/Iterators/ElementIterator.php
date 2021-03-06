<?php
/**
 * Iterator class for FluentDOM\DOM\Element objects
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright (c) 2009-2017 FluentDOM Contributors
 */

namespace FluentDOM\Utility\Iterators {

  use FluentDOM\DOM\Element;

  /**
   * Iterator class for FluentDOM\DOM\Element objects
   *
   * @method Element getOwner()
   */
  class ElementIterator extends IndexIterator implements \RecursiveIterator {

    /**
     * Check if current iterator pointer contains a valid element
     *
     * @return bool
     */
    public function valid(): bool {
      $owner = $this->getOwner();
      return
        NULL !== $owner->childNodes &&
        NULL !== $owner->childNodes->item($this->_position);
    }

    /**
     * Get current iterator element
     *
     * @return \DOMNode
     */
    public function current(): \DOMNode {
      return $this->getOwner()->childNodes->item($this->_position);
    }

    /**
     * Get child nodes of the current iterator element
     *
     * @throws \UnexpectedValueException
     * @return \RecursiveIterator
     */
    public function getChildren(): \RecursiveIterator {
      $element = $this->current();
      if ($element instanceof Element) {
        return new self($element);
      }
      throw new \UnexpectedValueException(
        'Called '.__METHOD__.' with invalid current element.'
      );
    }

    /**
     * Check if the current iterator element has children
     *
     * @return bool
     */
    public function hasChildren(): bool {
      return
        $this->valid() &&
        $this->current() instanceof Element &&
        NULL !== $this->current()->childNodes;
    }
  }
}