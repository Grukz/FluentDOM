<?php
/**
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
* @copyright Copyright (c) 2009-2014 Bastian Feder, Thomas Weinert
*/
header('Content-type: text/plain');

$xml = <<<XML
<html>
  <head>
    <title>Examples: FluentDOM\Query::wrapAll()</title>
  </head>
  <body>
    <div>
      <p>Hello</p>
      <p>cruel</p>
    </div>
    <div>
      <p>World</p>
    </div>
  </body>
</html>
XML;

require_once('../vendor/autoload.php');

echo FluentDOM($xml)
  ->find('//p')
  ->wrapAll('<div class="wrapper" />');

echo "\n\n";

echo FluentDOM($xml)
  ->find('//p')
  ->wrapAll('<div class="wrapper"><div>INNER</div></div>');
