<?php
namespace FluentDOM\Query {

  use FluentDOM\Exceptions;
  use FluentDOM\TestCase;

  require_once __DIR__.'/../../TestCase.php';

  class ManipulationWrapTest extends TestCase {

    protected $_directory = __DIR__;
    /**
     * @group Manipulation
     * @group ManipulationAround
     * @covers \FluentDOM\Query
     */
    public function testWrap() {
      $fd = $this->getQueryFixtureFromFunctionName(__FUNCTION__);
      $fd
        ->find('//p')
        ->wrap('<div class="outer"><div class="inner"></div></div>');
      $this->assertFluentDOMQueryEqualsXMLFile(__FUNCTION__, $fd);
    }

    /**
     * @group Manipulation
     * @group ManipulationAround
     * @covers \FluentDOM\Query
     */
    public function testWrapWithDomelement() {
      $fd = $this->getQueryFixtureFromFunctionName(__FUNCTION__);
      $document = $fd->document;
      $div = $document->createElement('div');
      $div->setAttribute('class', 'wrapper');
      $fd->find('//p')->wrap($div);
      $this->assertFluentDOMQueryEqualsXMLFile(__FUNCTION__, $fd);
    }

    /**
     * @group Manipulation
     * @group ManipulationAround
     * @covers \FluentDOM\Query
     */
    public function testWrapWithNodeList() {
      $fd = $this->getQueryFixtureFromFunctionName(__FUNCTION__);
      $divs = $fd->xpath->evaluate('//div[@class = "wrapper"]');
      $fd->find('//p')->wrap($divs);
      $this->assertFluentDOMQueryEqualsXMLFile(__FUNCTION__, $fd);
    }

    /**
     * @group Manipulation
     * @group ManipulationAround
     * @covers \FluentDOM\Query
     */
    public function testWrapWithInvalidArgument() {
      $this->expectException(Exceptions\LoadingError::class);
      $this->getQueryFixtureFromString(self::XML)
          ->find('//item')
          ->wrap(NULL);
    }

    /**
     * @group Manipulation
     * @group ManipulationAround
     * @covers \FluentDOM\Query
     */
    public function testWrapWithArray() {
      $fd = $this->getQueryFixtureFromFunctionName(__FUNCTION__);
      $document = $fd->document;
      $divs[0] = $document->createElement('div');
      $divs[0]->setAttribute('class', 'wrapper');
      $divs[1] = $document->createElement('div');
      $fd->find('//p')->wrap($divs);
      $this->assertFluentDOMQueryEqualsXMLFile(__FUNCTION__, $fd);
    }

    /**
     * @group Manipulation
     * @group ManipulationAround
     * @covers \FluentDOM\Query
     */
    public function testWrapWithCallback() {
      $fd = $this->getQueryFixtureFromFunctionName(__FUNCTION__);
      $fd->find('//p')->wrap(
        function($node, $index) {
          return '<div class="'.$node->textContent.'_'.$index.'" />';
        }
      );
      $this->assertFluentDOMQueryEqualsXMLFile(__FUNCTION__, $fd);
    }
  }
}