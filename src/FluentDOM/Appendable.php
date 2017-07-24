<?php
/**
 * Allow an object to be appendable to a FluentDOM\DOM\Element
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright (c) 2009-2017 Bastian Feder, Thomas Weinert
 */

namespace FluentDOM {

  use FluentDOM\DOM\Element;

  /**
   * Allow an object to be appendable to a FluentDOM\DOM\Element
   */
  interface Appendable {

    /**
     * Append the object to a FluentDOM\DOM\Element
     *
     * @param Element $parentNode
     * @return Element
     */
    function appendTo(Element $parentNode);
  }
}