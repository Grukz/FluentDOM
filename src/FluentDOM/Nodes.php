<?php
/**
 * Implements an extended replacement for DOMNodeList.
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright (c) 2009-2014 Bastian Feder, Thomas Weinert
 */

namespace FluentDOM {

  /**
   * Implements an extended replacement for DOMNodeList.
   *
   * @property string $contentType Output type - text/xml or text/html
   * @property callable $onPrepareSelector A callback to convert the selector into xpath
   * @property-read integer $length The amount of elements found by selector.
   * @property-read Document|\DOMDocument $document Internal DOMDocument object
   * @property-read \DOMXPath $xpath Internal XPath object
   */
  class Nodes implements \ArrayAccess, \Countable, \IteratorAggregate {

    /**
     * @var Xpath
     */
    private $_xpath = NULL;

    /**
     * @var array
     */
    private $_namespaces = [];

    /**
     * @var \DOMDocument
     */
    private $_document = NULL;

    /**
     * Content type for output (xml, text/xml, html, text/html).
     * @var string $_contentType
     */
    private $_contentType = 'text/xml';

    /**
     * A list of loaders for different data sources
     * @var Loadable $loaders
     */
    private $_loaders = NULL;

    /**
     * A callback used to convert the selector to xpath before use
     *
     * @var callable
     */
    private $_onPrepareSelector = NULL;

    /**
     * @var Nodes|NULL
     */
    protected $_parent = NULL;

    /**
     * @var \DOMNode[]
     */
    protected $_nodes = array();

    /**
     * Use document context for expression (not selected nodes).
     * @var boolean $_useDocumentContext
     */
    protected $_useDocumentContext = TRUE;

    /**
     * @param mixed $source
     * @param null|string $contentType
     */
    public function __construct($source = NULL, $contentType = 'text/xml') {
      if (isset($source)) {
        $this->load($source, $contentType);
      } elseif (isset($contentType)) {
        $this->setContentType($contentType);
      }
    }
    /**
     * Load a $source. The type of the source depends on the loaders. If no explicit loaders are set
     * it will use a set of default loaders for xml/html and json.
     *
     * @param mixed $source
     * @param string $contentType optional, default value 'text/xml'
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function load($source, $contentType = 'text/xml') {
      $dom = FALSE;
      $this->_useDocumentContext = TRUE;
      if ($source instanceof Nodes) {
        $dom = $source->getDocument();
      } elseif ($source instanceof \DOMDocument) {
        $dom = $source;
      } elseif ($source instanceof \DOMNode) {
        $dom = $source->ownerDocument;
        $this->_nodes = array($source);
        $this->_useDocumentContext = FALSE;
      } elseif ($this->loaders()->supports($contentType)) {
        $dom = $this->loaders()->load($source, $contentType);
      }
      if ($dom instanceof \DOMDocument) {
        $this->_document = $dom;
        $this->setContentType($contentType, TRUE);
        unset($this->_xpath);
        $this->applyNamespaces();
        return $this;
      } else {
        throw new \InvalidArgumentException(
          "Can not load: ".(is_object($source) ? get_class($source) : gettype($source))
        );
      }
    }

    /**
     * Set the loaders list.
     *
     * @param Loadable|array|\Traversable $loaders
     * @throws \InvalidArgumentException
     * @return Loadable
     */
    public function loaders($loaders = NULL) {
      if (isset($loaders)) {
        if ($loaders instanceOf Loadable) {
          $this->_loaders = $loaders;
        } elseif (is_array($loaders) || $loaders instanceOf \Traversable) {
          $this->_loaders = new Loaders($loaders);
        } else {
          throw new \InvalidArgumentException(
            "Invalid loader(s) argument."
          );
        }
      } elseif (NULL === $this->_loaders) {
        $this->_loaders = new Loaders(
          [
            new Loader\Xml(),
            new Loader\Html(),
            new Loader\Json()
          ]
        );
      }
      return $this->_loaders;
    }

    /**
     * Formats the current document, resets internal node array and other properties.
     *
     * The document is saved and reloaded, all variables with DOMNodes
     * of this document will get invalid.
     *
     * @param string $contentType
     * @return Nodes
     */
    public function formatOutput($contentType = NULL) {
      if (isset($contentType)) {
        $this->setContentType($contentType);
      }
      $this->_nodes = array();
      $this->_useDocumentContext = TRUE;
      $this->_parent = NULL;
      $this->_document->preserveWhiteSpace = FALSE;
      $this->_document->formatOutput = TRUE;
      if (!empty($this->_document->documentElement)) {
        $this->_document->loadXML($this->_document->saveXML());
      }
      return $this;
    }

    /**
     * The item() method is used to access elements in the node list,
     * like in a DOMNodelist.
     *
     * @param integer $position
     * @return \DOMElement|\DOMNode
     */
    public function item($position) {
      if (isset($this->_nodes[$position])) {
        return $this->_nodes[$position];
      }
      return NULL;
    }

    /**
     * @return Xpath
     */
    public function xpath() {
      if ($this->_document instanceof Document) {
        return $this->_document->xpath();
      } elseif (isset($this->_xpath) && $this->_xpath->document === $this->_document) {
        return $this->_xpath;
      } else {
        $this->_xpath = new Xpath($this->getDocument());
        $this->applyNamespaces();
        return $this->_xpath;
      }
    }

    /**
     * Register a namespace for selectors/expressions
     *
     * @param string $prefix
     * @param string $namespace
     */
    public function registerNamespace($prefix, $namespace) {
      $this->_namespaces[$prefix] = $namespace;
      $dom = $this->getDocument();
      if ($dom instanceOf Document) {
        $dom->registerNamespace($prefix, $namespace);
      } elseif (isset($this->_xpath)) {
        $this->_xpath->registerNamespace($prefix, $namespace);
      }
    }

    /**
     * apply stored namespaces to attached document or xpath object
     */
    private function applyNamespaces() {
      $dom = $this->getDocument();
      if ($dom instanceof Document) {
        foreach ($this->_namespaces as $prefix => $namespace) {
          $dom->registerNamespace($prefix, $namespace);
        }
      } elseif (isset($this->_xpath)) {
        foreach ($this->_namespaces as $prefix => $namespace) {
          $this->_xpath->registerNamespace($prefix, $namespace);
        }
      }
    }

    /**
     * Create a new instance of the same class with $this as the parent. This is used for the chaining.
     *
     * @param array|\Traversable|\DOMNode|Nodes $elements
     * @return Nodes
     */
    public function spawn($elements = NULL) {
      $result = clone $this;
      $result->_parent = $this;
      $result->_document = $this->getDocument();
      $result->_xpath = $this->xpath();
      $result->_nodes = array();
      if (isset($elements)) {
        $result->push($elements);
      }
      return $result;
    }

    /**
     * Return the parent FluentDOM\Nodes object.
     *
     * @return Nodes
     */
    public function end() {
      if ($this->_parent instanceof Nodes) {
        return $this->_parent;
      } else {
        return $this;
      }
    }

    /**
     * Push new element(s) an the internal element list
     *
     * @param \DOMNode|\Traversable|array|NULL $elements
     * @param boolean $ignoreTextNodes ignore text nodes
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    public function push($elements, $ignoreTextNodes = FALSE) {
      if ($this->isNode($elements, $ignoreTextNodes)) {
        $elements = array($elements);
      }
      if ($nodes = $this->isNodeList($elements)) {
        $this->_useDocumentContext = FALSE;
        foreach ($nodes as $index => $node) {
          if ($this->isNode($node, $ignoreTextNodes)) {
            if ($node->ownerDocument === $this->_document) {
              $this->_nodes[] = $node;
            } else {
              throw new \OutOfBoundsException(
                sprintf(
                  'Node #%d is not a part of this document', $index
                )
              );
            }
          }
        }
      } elseif (!is_null($elements)) {
        throw new \InvalidArgumentException('Invalid elements variable.');
      }
    }

    protected function uniqueSortNodes() {
      $this->_nodes = $this->unique($this->_nodes);
    }

    /**
     * Setter for Nodes::_contentType property
     *
     * @param string $value
     * @param bool $silentFallback
     * @throws \Exception
     * @throws \UnexpectedValueException
     */
    private function setContentType($value, $silentFallback = FALSE) {
      switch (strtolower($value)) {
      case 'xml' :
      case 'application/xml' :
      case 'text/xml' :
        $newContentType = 'text/xml';
        break;
      case 'html' :
      case 'text/html' :
        $newContentType = 'text/html';
        break;
      default :
        if ($silentFallback) {
          $newContentType = 'text/xml';
        } else {
          throw new \UnexpectedValueException('Invalid content type value');
        }
      }
      if (isset($this->_parent) && $this->_contentType != $newContentType) {
        $this->_parent->contentType = $newContentType;
      }
      $this->_contentType = $newContentType;
    }

    /**
     * Get the associated DOM, create one if here isn't one yet.
     *
     * @return \DOMDocument|Document
     */
    public function getDocument() {
      if (!($this->_document instanceof \DOMDocument)) {
        $this->_document = new Document();
        $this->applyNamespaces();
      }
      return $this->_document;
    }

    /**************
     * Interfaces
     *************/

    /**
     * Countable interface
     *
     * @return int
     */
    public function count() {
      return count($this->_nodes);
    }

    /**
     * IteratorAggregate interface
     *
     * @return Iterators\NodesIterator
     */
    public function getIterator() {
      return new Iterators\NodesIterator($this);
    }

    /**
     * Retrieve the matched DOM nodes in an array.
     *
     * @return \DOMNode[]
     */
    public function toArray() {
      return $this->_nodes;
    }

    /*
     * Interface - ArrayAccess
     */

    /**
     * Check if index exists in internal array
     *
     * @example interfaces/ArrayAccess.php Usage Example: ArrayAccess Interface
     * @param integer $offset
     * @return boolean
     */
    public function offsetExists($offset) {
      return isset($this->_nodes[$offset]);
    }

    /**
     * Get element from internal array
     *
     * @example interfaces/ArrayAccess.php Usage Example: ArrayAccess Interface
     * @param integer $offset
     * @return \DOMElement|\DOMNode|NULL
     */
    public function offsetGet($offset) {
      return isset($this->_nodes[$offset]) ? $this->_nodes[$offset] : NULL;
    }

    /**
     * If somebody tries to modify the internal array throw an exception.
     *
     * @example interfaces/ArrayAccess.php Usage Example: ArrayAccess Interface
     * @param integer $offset
     * @param mixed $value
     * @throws \BadMethodCallException
     */
    public function offsetSet($offset, $value) {
      throw new \BadMethodCallException('List is read only');
    }

    /**
     * If somebody tries to remove an element from the internal array throw an exception.
     *
     * @example interfaces/ArrayAccess.php Usage Example: ArrayAccess Interface
     * @param integer $offset
     * @throws \BadMethodCallException
     */
    public function offsetUnset($offset) {
      throw new \BadMethodCallException('List is read only');
    }

    /**
     * Virtual properties, validate existence
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name) {
      switch ($name) {
      case 'contentType' :
      case 'length' :
      case 'xpath' :
        return TRUE;
      case 'document' :
        return isset($this->_document);
      }
      return FALSE;
    }

    /**
     * Virtual properties, read property
     *
     * @param string $name
     * @throws \UnexpectedValueException
     * @return mixed
     */
    public function __get($name) {
      switch ($name) {
      case 'contentType' :
        return $this->_contentType;
      case 'document' :
        return $this->getDocument();
      case 'length' :
        return count($this->_nodes);
      case 'xpath' :
        return $this->xpath();
      case 'onPrepareSelector' :
        return $this->_onPrepareSelector;
      default :
        return NULL;
      }
    }

    /**
     * Block changing the readonly dynamic property
     *
     * @param string $name
     * @param mixed $value
     * @throws \BadMethodCallException
     */
    public function __set($name, $value) {
      switch ($name) {
      case 'contentType' :
        $this->setContentType($value);
        break;
      case 'onPrepareSelector' :
        if ($callback = $this->isCallable($value, TRUE, FALSE)) {
          $this->_onPrepareSelector = $callback;
        }
        break;
      case 'document' :
      case 'length' :
      case 'xpath' :
        throw new \BadMethodCallException('Can not set readonly value.');
      default :
        $this->$name = $value;
        break;
      }
    }

    /**
     * Throws an exception if somebody tries to unset one
     * of the dynamic properties
     *
     * @param string $name
     * @throws \BadMethodCallException
     */
    public function __unset($name) {
      switch ($name) {
      case 'contentType' :
      case 'document' :
      case 'length' :
      case 'xpath' :
        throw new \BadMethodCallException(
          sprintf(
            'Can not unset property %s::$%s',
            get_class($this),
            $name
          )
        );
      }
      throw new \BadMethodCallException(
        sprintf(
          'Can not unset non existing property %s::$%s',
          get_class($this),
          $name
        )
      );
    }

    /**
     * Return the XML output of the internal dom document
     *
     * @return string
     */
    public function __toString() {
      switch ($this->contentType) {
      case 'html' :
      case 'text/html' :
        return $this->document->saveHTML();
      default :
        return $this->document->saveXML();
      }
    }

    /**
     * Check if the DOMNode is DOMElement or DOMText with content.
     * It returns the node or NULL.
     *
     * @param mixed $node
     * @param boolean $ignoreTextNodes
     * @param string|NULL $selector
     * @return \DOMElement|\DOMText|\DOMCdataSection
     */
    public function isNode($node, $ignoreTextNodes = FALSE, $selector = NULL) {
      if (
        Constraints::isNode($node, $ignoreTextNodes) &&
        (
          empty($selector) ||
          $this->matches($selector, $node)
        )
      ) {
        return $node;
      }
      return NULL;
    }

    /**
     * Check if $elements is a traversable node list. It returns
     * the $elements or NULL
     *
     * @param mixed $elements
     * @return \Traversable|array
     */
    public function isNodeList($elements) {
      return Constraints::isNodeList($elements);
    }

    /**
     * check if parameter is a valid callback function. It returns
     * the callable or NULL.
     *
     * If $silent is disabled, an exception is thrown for invalid callbacks
     *
     * @param mixed $callback
     * @param boolean $allowGlobalFunctions
     * @param boolean $silent (no InvalidArgumentException)
     * @throws \InvalidArgumentException
     * @return callable|NULL
     */
    public function isCallable($callback, $allowGlobalFunctions = FALSE, $silent = TRUE) {
      return Constraints::isCallable($callback, $allowGlobalFunctions, $silent);
    }

    /**
     * Use callback to convert selector if it is set.
     *
     * @param string $selector
     * @return string
     */
    private function prepareSelector($selector) {
      if (isset($this->_onPrepareSelector)) {
        return call_user_func($this->_onPrepareSelector, $selector);
      }
      return $selector;
    }

    /**
     * Test that selector matches context and return true/false
     *
     * @param string $selector
     * @param \DOMNode $context optional, default value NULL
     * @return boolean
     */
    public function matches($selector, \DOMNode $context = NULL) {
      $check = $this->xpath->evaluate(
        $this->prepareSelector($selector), $context
      );
      if ($check instanceof \DOMNodeList) {
        return $check->length > 0;
      } else {
        return (bool)$check;
      }
    }

    /**
     * Match selector against context and return matched elements.
     *
     * @param string|\DOMNode|array|\Traversable $selector
     * @param \DOMNode $context optional, default value NULL
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function getNodes($selector, \DOMNode $context = NULL) {
      if ($this->isNode($selector)) {
        return array($selector);
      } elseif (is_string($selector)) {
        $result = $this->xpath()->evaluate(
          $this->prepareSelector($selector), $context, FALSE
        );
        if (!($result instanceof \Traversable)) {
          throw new \InvalidArgumentException('Given selector did not return an node list.');
        }
        return iterator_to_array($result);
      } elseif ($nodes = $this->isNodeList($selector)) {
        return is_array($nodes) ? $nodes : iterator_to_array($nodes);
      } elseif ($callback = $this->isCallable($selector)) {
        if ($nodes = $callback($context)) {
          return is_array($nodes) ? $nodes : iterator_to_array($nodes);
        }
        return array();
      }
      throw new \InvalidArgumentException('Invalid selector');
    }

    /**
     * Execute a function within the context of every matched element.
     *
     * If $elementsOnly is set to TRUE, only element nodes are used.
     *
     * If $elementsOnly is a callable the return value defines if
     * it is called for that node.
     *
     * @param callable $function
     * @param bool|callable $elementsFilter
     * @return $this
     */
    public function each(callable $function, $elementsFilter = FALSE) {
      if (TRUE === $elementsFilter) {
        $filter = function($node) {
          return $node instanceof \DOMElement;
        };
      } else {
        $filter = $this->isCallable($elementsFilter);
      }
      foreach ($this->_nodes as $index => $node) {
        if (NULL === $filter || $filter($node, $index)) {
          call_user_func($function, $node, $index);
        }
      }
      return $this;
    }

    /**
     * Searches for descendant elements that match the specified expression.
     *
     * @example find.php Usage Example: FluentDOM::find()
     * @param string $selector selector
     * @param boolean $useDocumentContext ignore current node list
     * @return Nodes
     */
    public function find($selector, $useDocumentContext = FALSE) {
      if ($useDocumentContext ||
        $this->_useDocumentContext) {
        return $this->spawn($this->getNodes($selector));
      } else {
        $result = $this->spawn();
        foreach ($this->_nodes as $context) {
          $result->push($this->getNodes($selector, $context));
        }
        return $result;
      }
    }

    /**
     * Search for a given element from among the matched elements.
     *
     * @param NULL|string|\DOMNode|\Traversable $selector
     * @return integer
     */
    public function index($selector = NULL) {
      if (count($this->_nodes) > 0) {
        if (is_null($selector)) {
          return $this->xpath()->evaluate(
            'count(
              preceding-sibling::node()[
                self::* or (self::text() and normalize-space(.) != "")
              ]
            )',
            $this->_nodes[0]
          );
        } else {
          if (is_string($selector)) {
            $callback = function(\DOMNode $node) use ($selector) {
              return $this->matches($selector, $node);
            };
          } else {
            if (
              ($selector instanceof \DOMNodeList || $selector instanceof Nodes) &&
              $selector->length > 0
            ) {
              /** @var \DOMNodeList|Nodes $selector */
              $targetNode = $selector->item(0);
            } elseif (is_array($selector)) {
              $targetNode = reset($selector);
            } else {
              $targetNode = $selector;
            }
            if (!($targetNode instanceof \DOMNode)) {
              return -1;
            }
            $callback = function(\DOMNode $node) use ($targetNode) {
              return $node->isSameNode($targetNode);
            };
          }
          foreach ($this->_nodes as $index => $node) {
            if ($callback($node)) {
              return $index;
            }
          }
        }
      }
      return -1;
    }

    /**
     * Sorts an array of DOM nodes based on document position, in place, with the duplicates removed.
     * Note that this only works on arrays of DOM nodes, not strings or numbers.
     *
     * @param \DOMNode[] $array array of DOM nodes
     * @throws \InvalidArgumentException
     * @return array
     */
    public function unique(array $array) {
      $sortable = array();
      $unsortable = array();
      foreach ($array as $node) {
        if (!($node instanceof \DOMNode)) {
          throw new \InvalidArgumentException(
            sprintf(
              'Array must only contain dom nodes, found "%s".',
              is_object($node) ? get_class($node) : gettype($node)
            )
          );
        }
        if (
          ($node->parentNode instanceof \DOMNode) ||
          $node === $node->ownerDocument->documentElement) {
          $position = (integer)$this->xpath()->evaluate('count(preceding::node())', $node);
          /* use the document position as index, ignore duplicates */
          if (!isset($sortable[$position])) {
            $sortable[$position] = $node;
          }
        } else {
          $hash = spl_object_hash($node);
          /* use the object hash as index, ignore duplicates */
          if (!isset($unsortable[$hash])) {
            $unsortable[$hash] = $node;
          }
        }
      }
      ksort($sortable, SORT_NUMERIC);
      $result = array_values($sortable);
      array_splice($result, count($result), 0, array_values($unsortable));
      return $result;
    }
  }
}