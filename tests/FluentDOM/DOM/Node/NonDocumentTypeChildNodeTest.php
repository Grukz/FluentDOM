<?php

namespace FluentDOM\Node {

  use FluentDOM\DOM\Document;
  use FluentDOM\TestCase;

  require_once __DIR__.'/../../TestCase.php';

  class NonDocumentTypeChildNodeTest extends TestCase {

    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testIssetNextElementSiblingExpectingTrue() {
      $document = new Document();
      $document->loadXML('<foo><!-- START -->TEXT<bar index="1"/></foo>');
      $this->assertTrue(isset($document->documentElement->firstChild->nextElementSibling));
    }

    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testIssetNextElementSiblingExpectingFalse() {
      $document = new Document();
      $document->loadXML('<foo><!-- START -->TEXT</foo>');
      $this->assertFalse(isset($document->documentElement->firstChild->nextElementSibling));
    }

    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testGetNextElementSibling() {
      $document = new Document();
      $document->loadXML('<foo><bar index="0"/>TEXT<bar index="1"/></foo>');
      $node = $document->documentElement->firstChild->nextElementSibling;
      $this->assertEquals(
        '<bar index="1"/>',
        $node->saveXml()
      );
    }

    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testGetNextElementSiblingFromCommentNode() {
      $document = new Document();
      $document->loadXML('<foo><!-- START -->TEXT<bar index="1"/></foo>');
      $node = $document->documentElement->firstChild->nextElementSibling;
      $this->assertEquals(
        '<bar index="1"/>',
        $node->saveXml()
      );
    }

    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testGetNextElementSiblingExpectingNull() {
      $document = new Document();
      $document->loadXML('<foo><bar index="0"/>TEXT</foo>');
      $this->assertNull(
        $document->documentElement->firstChild->nextElementSibling
      );
    }

    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testIssetPreviousElementSiblingExpectingTrue() {
      $document = new Document();
      $document->loadXML('<foo><bar index="0"/>TEXT<!-- START --></foo>');
      $this->assertTrue(isset($document->documentElement->lastChild->previousElementSibling));
    }
    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testIssetPreviousElementSiblingExpectingFalse() {
      $document = new Document();
      $document->loadXML('<foo>TEXT<!-- START --></foo>');
      $this->assertFalse(isset($document->documentElement->lastChild->previousElementSibling));
    }

    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testGetPreviousElementSibling() {
      $document = new Document();
      $document->loadXML('<foo><bar index="0"/>TEXT<bar index="1"/></foo>');
      $node = $document->documentElement->lastChild->previousElementSibling;
      $this->assertEquals(
        '<bar index="0"/>',
        $node->saveXml()
      );
    }

    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testPreviousElementSiblingFromCommentNode() {
      $document = new Document();
      $document->loadXML('<foo><bar index="0"/>TEXT<!-- START --></foo>');
      $node = $document->documentElement->lastChild->previousElementSibling;
      $this->assertEquals(
        '<bar index="0"/>',
        $node->saveXml()
      );
    }

    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testPreviousElementSiblingExpectingNull() {
      $document = new Document();
      $document->loadXML('<foo>TEXT<bar index="1"/></foo>');
      $this->assertNull(
        $document->documentElement->lastChild->previousElementSibling
      );
    }

    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testGetParentPropertyFromCommentNode() {
      $document = new Document();
      $document->loadXML('<foo><!--comment--></foo>');
      $this->assertEquals(
        'comment',
        $document->documentElement->firstChild->textContent
      );
    }

    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testSetNextElementChildExpectingException() {
      $document = new Document();
      $document->loadXML('<foo><!--comment--></foo>');
      $this->expectException(\BadMethodCallException::class);
      $document->documentElement->firstChild->nextElementSibling = $document->createElement('foo');
    }

    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testSetPreviousElementChildExpectingException() {
      $document = new Document();
      $document->loadXML('<foo><!--comment--></foo>');
      $this->expectException(\BadMethodCallException::class);
      $document->documentElement->firstChild->previousElementSibling = $document->createElement('foo');
    }

    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testSetUnknownProperty() {
      $document = new Document();
      $document->loadXML('<foo><!--comment--></foo>');
      $node = $document->documentElement->firstChild;
      $node->SOME_PROPERTY = 'success';
      $this->assertEquals('success', $node->SOME_PROPERTY);
    }

    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testUnsetUnknownProperty() {
      $document = new Document();
      $document->loadXML('<foo><!--comment--></foo>');
      $node = $document->documentElement->firstChild;
      $this->expectError(E_NOTICE);
      unset($node->SOME_PROPERTY);
      $this->assertNull($node->SOME_PROPERTY);
    }

    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testIssetUnknownPropertyExpectingTrue() {
      $document = new Document();
      $document->loadXML('<foo><!--comment--></foo>');
      $node = $document->documentElement->firstChild;
      $node->SOME_PROPERTY = 'foo';
      $this->assertTrue(isset($node->SOME_PROPERTY));
    }

    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testIssetUnknownPropertyExpectingFalse() {
      $document = new Document();
      $document->loadXML('<foo><!--comment--></foo>');
      $node = $document->documentElement->firstChild;
      $this->assertFalse(isset($node->SOME_PROPERTY));
    }

    /**
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Implementation
     * @covers \FluentDOM\DOM\Node\NonDocumentTypeChildNode\Properties
     */
    public function testGetUnknownPropertyExpectingException() {
      $document = new Document();
      $document->loadXML('<foo><!--comment--></foo>');
      $node = $document->documentElement->firstChild;
      $this->expectError(E_NOTICE);
      $node->SOME_PROPERTY;
    }
  }
}