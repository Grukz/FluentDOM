<?php
namespace FluentDOM\Loader\Json {

  use FluentDOM\TestCase;

  require_once(__DIR__ . '/../../TestCase.php');

  class JsonDOMTest extends TestCase {

    /**
     * @covers FluentDOM\Loader\JsonDOM
     */
    public function testSupportsExpectingTrue() {
      $loader = new JsonDOM();
      $this->assertTrue($loader->supports('json'));
    }

    /**
     * @covers FluentDOM\Loader\JsonDOM
     */
    public function testSupportsExpectingFalse() {
      $loader = new JsonDOM();
      $this->assertFalse($loader->supports('text/xml'));
    }

    /**
     * @covers FluentDOM\Loader\JsonDOM
     */
    public function testLoadWithValidJsonDOM() {
      $loader = new JsonDOM();
      $this->assertInstanceOf(
        'DOMDocument',
        $dom = $loader->load(
          '{"foo":"bar"}',
          'json'
        )
      );
      $this->assertXmlStringEqualsXmlString(
        '<?xml version="1.0" encoding="UTF-8"?>'.
        '<json:json xmlns:json="urn:carica-json-dom.2013">'.
        '<foo>bar</foo>'.
        '</json:json>',
        $dom->saveXml()
      );
    }

    /**
     * @covers FluentDOM\Loader\JsonDOM
     */
    public function testLoadWithValidFile() {
      $loader = new JsonDOM();
      $this->assertInstanceOf(
        'DOMDocument',
        $dom = $loader->load(
          __DIR__.'/TestData/loader.json',
          'json'
        )
      );
      $this->assertXmlStringEqualsXmlString(
        '<?xml version="1.0" encoding="UTF-8"?>'.
        '<json:json xmlns:json="urn:carica-json-dom.2013">'.
        '<foo>bar</foo>'.
        '</json:json>',
        $dom->saveXml()
      );
    }

    /**
     * @covers FluentDOM\Loader\JsonDOM
     */
    public function testLoadWithValidStructure() {
      $loader = new JsonDOM();
      $json = new \stdClass();
      $json->foo = 'bar';
      $this->assertInstanceOf(
        'DOMDocument',
        $dom = $loader->load(
          $json, 'json'
        )
      );
      $this->assertXmlStringEqualsXmlString(
        '<?xml version="1.0" encoding="UTF-8"?>'.
        '<json:json xmlns:json="urn:carica-json-dom.2013">'.
        '<foo>bar</foo>'.
        '</json:json>',
        $dom->saveXml()
      );
    }

    /**
     * @covers FluentDOM\Loader\JsonDOM
     */
    public function testLoadWithValidJsonVerbose() {
      $loader = new JsonDOM(JsonDOM::OPTION_VERBOSE);
      $this->assertInstanceOf(
        'DOMDocument',
        $dom = $loader->load(
          '{"foo":"bar"}',
          'json'
        )
      );
      $this->assertXmlStringEqualsXmlString(
        '<?xml version="1.0" encoding="UTF-8"?>'.
        '<json:json'.
        ' xmlns:json="urn:carica-json-dom.2013"'.
        ' json:type="object">'.
        '<foo json:name="foo" json:type="string">bar</foo>'.
        '</json:json>',
        $dom->saveXml()
      );
    }
    /**
     * @covers FluentDOM\Loader\JsonDOM
     */
    public function testLoadWithDifferentDataTypes() {
      $loader = new JsonDOM();
      $dom = $loader->load(
        json_encode(
          array(
            'boolean' => TRUE,
            'int' => 42,
            'null' => NULL,
            'string' => 'Foo',
            'array' => array(21),
            'object' => new \stdClass()
          )
        ),
        'json'
      );
      $this->assertXmlStringEqualsXmlString(
        '<?xml version="1.0" encoding="UTF-8"?>
         <json:json xmlns:json="urn:carica-json-dom.2013">
           <boolean json:type="boolean">true</boolean>
           <int json:type="number">42</int>
           <null json:type="null"/>
           <string>Foo</string>
           <array json:type="array">
             <_ json:type="number">21</_>
           </array>
           <object json:type="object"/>
         </json:json>',
        $dom->saveXml()
      );
    }

    /**
     * @covers FluentDOM\Loader\JsonDOM
     */
    public function testLoadWithInvalidSourceExpectingNull() {
      $loader = new JsonDOM();
      $this->assertNull(
        $loader->load(
          NULL,
          'json'
        )
      );
    }

    /**
     * @covers FluentDOM\Loader\JsonDOM
     */
    public function testLoadWithInvalidJsonStringExpectingException() {
      $loader = new JsonDOM();
      $this->setExpectedException('UnexpectedValueException');
      $loader->load(
        '{foo}}',
        'json'
      );
    }

    /**
     * @covers FluentDOM\Loader\JsonDOM
     */
    public function testLoadStoppingAtMaxDepth() {
      $loader = new JsonDOM(0, 1);
      $this->assertXmlStringEqualsXmlString(
        '<?xml version="1.0" encoding="UTF-8"?>
         <json:json xmlns:json="urn:carica-json-dom.2013"><foo/></json:json>',
        $loader
          ->load(json_encode(['foo' => [1, 2, 3]]), 'json')
          ->saveXML()
      );
    }

    /**
     * @covers FluentDOM\Loader\JsonDOM
     */
    public function testLoadWithEmptyArray() {
      $loader = new JsonDOM(0, 1);
      $this->assertXmlStringEqualsXmlString(
        '<?xml version="1.0" encoding="UTF-8"?>
         <json:json xmlns:json="urn:carica-json-dom.2013" json:type="array"/>',
        $loader
          ->load('[]', 'json')
          ->saveXML()
      );
    }
  }
}