<?php
namespace FluentDOM\Query {

  use FluentDOM\Query;
  use FluentDOM\TestCase;

  require_once __DIR__.'/../TestCase.php';

  class DataTest extends TestCase {

    protected $_directory = __DIR__;
    /**
     * @covers \FluentDOM\Query\Data::__construct
     */
    public function testConstructor() {
      $document = new \DOMDocument();
      $document->appendChild($document->createElement('sample'));
      $data = new Data($document->documentElement);
      $this->assertAttributeSame(
        $document->documentElement, '_node', $data
      );
    }

    /**
     * @covers \FluentDOM\Query\Data::toArray
     * @covers \FluentDOM\Query\Data::isDataProperty
     */
    public function testToArrayWithSeveralAttributes() {
      $document = new \DOMDocument();
      $document->loadXML(
        '<div data-role="page" data-hidden="true" data-options=\'{"name":"John"}\'></div>'
      );
      $options = new \stdClass();
      $options->name = 'John';
      $data = new Data($document->documentElement);
      $this->assertEquals(
        [
          'role' => 'page',
          'hidden' => TRUE,
          'options' => $options
        ],
        $data->toArray()
      );
    }

    /**
     * @covers \FluentDOM\Query\Data::toArray
     * @covers \FluentDOM\Query\Data::decodeName
     */
    public function testToArrayWithComplexAttribute() {
      $document = new \DOMDocument();
      $document->loadXML(
        '<div data-options-name="John"></div>'
      );
      $data = new Data($document->documentElement);
      $this->assertEquals(
        ['optionsName' => 'John'],
        $data->toArray()
      );
    }

    /**
     * @covers \FluentDOM\Query\Data::getIterator
     */
    public function testGetIterator() {
      $document = new \DOMDocument();
      $document->loadXML(
        '<div data-role="page" data-hidden="true"></div>'
      );
      $data = new Data($document->documentElement);
      $this->assertEquals(
        [
          'role' => 'page',
          'hidden' => TRUE
        ],
        iterator_to_array($data)
      );
    }

    /**
     * @covers \FluentDOM\Query\Data::count
     */
    public function testCountExpectingZero() {
      $document = new \DOMDocument();
      $document->loadXML(
        '<div></div>'
      );
      $data = new Data($document->documentElement);
      $this->assertEquals(
        0, count($data)
      );
    }

    /**
     * @covers \FluentDOM\Query\Data::count
     */
    public function testCountExpectingTwo() {
      $document = new \DOMDocument();
      $document->loadXML(
        '<div data-role="page" data-hidden="true"></div>'
      );
      $data = new Data($document->documentElement);
      $this->assertEquals(
        2, count($data)
      );
    }

    /**
     * @covers \FluentDOM\Query\Data::__isset
     */
    public function testMagicMethodIssetExpectingTrue() {
      $document = new \DOMDocument();
      $document->loadXML('<node data-truth="true"/>');
      $data = new Data($document->documentElement);
      $this->assertTrue(isset($data->truth));
    }

    /**
     * @covers \FluentDOM\Query\Data::__isset
     */
    public function testMagicMethodIssetExpectingFalse() {
      $document = new \DOMDocument();
      $document->loadXML('<node data-truth="true"/>');
      $data = new Data($document->documentElement);
      $this->assertFalse(isset($data->lie));
    }

    /**
     * @covers \FluentDOM\Query\Data::__get
     * @covers \FluentDOM\Query\Data::encodeName
     * @covers \FluentDOM\Query\Data::decodeValue
     * @dataProvider provideDataAttributes
     */
    public function testMagicMethodGet($expected, $name, $xml) {
      $document = new \DOMDocument();
      $document->loadXML($xml);
      $data = new Data($document->documentElement);
      $this->assertEquals(
        $expected,
        $data->$name
      );
    }

    /**
     * @covers \FluentDOM\Query\Data::__set
     * @covers \FluentDOM\Query\Data::encodeName
     * @covers \FluentDOM\Query\Data::encodeValue
     * @dataProvider provideDataValues
     */
    public function testMagicMethodSet($expectedXml, $name, $value) {
      $document = new \DOMDocument();
      $document->appendChild($document->createElement('node'));
      $data = new Data($document->documentElement);
      $data->$name = $value;
      $this->assertEquals(
        $expectedXml, $document->saveXml($document->documentElement)
      );
    }

    /**
     * @covers \FluentDOM\Query\Data::__unset
     * @covers \FluentDOM\Query\Data::encodeName
     * @dataProvider provideDataValues
     */
    public function testMagicMethodUnset() {
      $document = new \DOMDocument();
      $document->loadXML('<node data-truth="true"/>');
      $data = new Data($document->documentElement);
      unset($data->truth);
      $this->assertEquals(
        '<node/>', $document->saveXML($document->documentElement)
      );
    }

    public static function provideDataAttributes() {
      return [
        'string' => ['World', 'Hello', '<node data-hello="World"/>'],
        'boolean true' => [TRUE, 'truth', '<node data-truth="true"/>'],
        'boolean false' => [FALSE, 'lie', '<node data-lie="false"/>'],
        'array' => [
          ['1', '2'],
          'list',
          '<node data-list="[&quot;1&quot;,&quot;2&quot;]"/>'
        ],
        'object' => [
          self::createObjectFromArray(['foo' => 'bar']),
          'object',
          '<node data-object="{&quot;foo&quot;:&quot;bar&quot;}"/>'
        ],
        'invalid object' => [NULL, 'object', '<node data-object=\'{{"foo":"bar"}\'/>'],
        'invalid attrbute' => [NULL, 'unknown', '<node/>'],
        'complex name' => [1, 'complexName', '<node data-complex-name="1"/>'],
        'abbreviation' => [1, 'someABBRName', '<node data-some-abbr-name="1"/>']
      ];
    }

    public static function provideDataValues() {
      return [
        'string' => ['<node data-hello="World"/>', 'hello', 'World'],
        'boolean true' => ['<node data-truth="true"/>', 'truth', TRUE],
        'boolean false' => ['<node data-lie="false"/>', 'lie', FALSE],
        'array' => ['<node data-list="[&quot;1&quot;,&quot;2&quot;]"/>', 'list', ['1', '2']],
        'object' => [
          '<node data-object="{&quot;foo&quot;:&quot;bar&quot;}"/>',
          'object',
          self::createObjectFromArray(['foo' => 'bar'])
        ],
        'complex name' => ['<node data-say-hello="World"/>', 'sayHello', 'World'],
        'abbreviation' => ['<node data-some-abbr-name="1"/>', 'someABBRName', 1],
      ];
    }

    public static function createObjectFromArray($array) {
      $result = new \stdClass();
      foreach ($array as $key => $value) {
        $result->$key = $value;
      }
      return $result;
    }

    /*****************************
     * Method interface on Query
     *****************************/

    /**
     * @group AttributesData
     * @covers \FluentDOM\Query::data
     */
    public function testDataRead() {
      $fd = $this->getQueryFixtureFromString('<sample data-foo="bar"/>')->find('//sample');
      $this->assertEquals('bar', $fd->data('foo'));
    }

    /**
     * @group AttributesData
     * @covers \FluentDOM\Query::data
     */
    public function testDataReadWithoutElement() {
      $fd = $this->getQueryFixtureFromString('<sample/>');
      $this->assertEquals('', $fd->data('dummy'));
    }

    /**
     * @group AttributesData
     * @covers \FluentDOM\Query::data
     * @covers \FluentDOM\Query::getSetterValues
     */
    public function testDataWrite() {
      $fd = $this->getQueryFixtureFromString('<sample/>')->find('//sample');
      $fd->data('foo', 'bar');
      $this->assertEquals(
        '<sample data-foo="bar"/>',
        $fd->document->saveXml($fd->document->documentElement)
      );
    }

    /**
     * @group AttributesData
     * @covers \FluentDOM\Query::data
     * @covers \FluentDOM\Query::getSetterValues
     */
    public function testDataWriteWithNullValue() {
      $fd = $this->getQueryFixtureFromString('<sample/>')->find('//sample');
      $this->assertSame(
        $fd,
        $fd->data('foo', NULL)
      );
    }

    /**
     * @group AttributesData
     * @covers \FluentDOM\Query::data
     * @covers \FluentDOM\Query::getSetterValues
     */
    public function testDataWriteUsingArray() {
      $fd = $this->getQueryFixtureFromString('<sample/>')->find('//sample');
      $fd->data(['foo' => 'bar']);
      $this->assertEquals(
        '<sample data-foo="bar"/>',
        $fd->document->saveXml($fd->document->documentElement)
      );
    }

    /**
     * @group AttributesData
     * @covers \FluentDOM\Query::removeData
     * @covers \FluentDOM\Query::getNamesList
     */
    public function testRemoveData() {
      $fd = $this->getQueryFixtureFromString(
        '<sample data-foo="bar" data-bar="foo"/>'
      )->find('//sample');
      $fd->removeData('foo');
      $this->assertEquals(
        '<sample data-bar="foo"/>',
        $fd->document->saveXml($fd->document->documentElement)
      );
    }

    /**
     * @group AttributesData
     * @covers \FluentDOM\Query::removeData
     * @covers \FluentDOM\Query::getNamesList
     */
    public function testRemoveDataWithList() {
      $fd = $this->getQueryFixtureFromString(
        '<sample data-foo="bar" data-bar="foo"/>'
      )->find('//sample');
      $fd->removeData(['foo', 'bar']);
      $this->assertEquals(
        '<sample/>',
        $fd->document->saveXML($fd->document->documentElement)
      );
    }

    /**
     * @group AttributesData
     * @covers \FluentDOM\Query::removeData
     * @covers \FluentDOM\Query::getNamesList
     */
    public function testRemoveDataWithoutNamesRemovingAll() {
      $fd = $this->getQueryFixtureFromString(
        '<sample data-foo="bar" data-bar="foo"/>'
      )->find('//sample');
      $fd->removeData();
      $this->assertEquals(
        '<sample/>',
        $fd->document->saveXml($fd->document->documentElement)
      );
    }

    /**
     * @group AttributesData
     * @covers \FluentDOM\Query::removeData
     * @covers \FluentDOM\Query::getNamesList
     */
    public function testRemoveDataWithInvalidName() {
      $fd = $this->getQueryFixtureFromString(
        '<sample data-foo="bar" data-bar="foo"/>'
      )->find('//sample');
      $this->expectException(\InvalidArgumentException::class);
      $fd->removeData('');
    }

    /**
     * @group AttributesData
     * @covers \FluentDOM\Query::hasData
     */
    public function testHasDataExpectingTrue() {
      $fd = $this->getQueryFixtureFromString('<sample data-foo="bar"/>')->find('//sample');
      $this->assertTrue($fd->hasData());
    }

    /**
     * @group AttributesData
     * @covers \FluentDOM\Query::hasData
     */
    public function testHasDataExpectingFalse() {
      $fd = $this->getQueryFixtureFromString('<sample/>')->find('//sample');
      $this->assertFalse($fd->hasData());
    }

    /**
     * @group AttributesData
     * @covers \FluentDOM\Query::hasData
     */
    public function testHasDataOnEmptyFluentDomExpectingFalse() {
      $fd = $this->getQueryFixtureFromString('<sample/>');
      $this->assertFalse($fd->hasData());
    }

    /**
     * @group AttributesData
     * @covers \FluentDOM\Query::hasData
     */
    public function testHasDataOnElementExpectingTrue() {
      $fd = $this->getQueryFixtureFromString('<sample data-foo="bar"/>');
      $this->assertTrue($fd->hasData($fd->document->documentElement));
    }

    /**
     * @group AttributesData
     * @covers \FluentDOM\Query::hasData
     */
    public function testHasDataOnElementExpectingFalse() {
      $fd = $this->getQueryFixtureFromString('<sample/>');
      $this->assertFalse($fd->hasData($fd->document->documentElement));
    }


    /*****************************
     * Property interface on Query
     *****************************/

    /**
     * @group AttributesData
     * @covers \FluentDOM\Query::__get
     */
    public function testDataPropertyRead() {
      $fd = $this->getQueryFixtureFromString('<sample data-foo="bar"/>')->find('//sample');
      $this->assertEquals(['foo' => 'bar'], iterator_to_array($fd->data));
    }

    /**
     * @group AttributesData
     * @covers \FluentDOM\Query::__get
     */
    public function testDataPropertyReadOnEmptyListExpectingException() {
      $fd = new Query();
      $this->expectException(\UnexpectedValueException::class);
      $fd->data;
    }

    /**
     * @group AttributesData
     * @covers \FluentDOM\Query::__set
     */
    public function testDataPropertyWrite() {
      $fd = $this->getQueryFixtureFromString('<sample/>')->find('//sample');
      $fd->data = ['foo' => 'bar'];
      $this->assertXmlStringEqualsXmlString(
        '<sample data-foo="bar"/>',
        (string)$fd
      );
    }
  }
}