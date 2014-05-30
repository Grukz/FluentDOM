<?php
/**
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
* @copyright Copyright (c) 2009-2014 Bastian Feder, Thomas Weinert
*/
header('Content-type: text/plain');

$xml = <<<XML
<html>
  <head>
    <title>Examples: FluentDOM\Query::wrap()</title>
  </head>
  <body>
    <p>Hello</p>
    <p>cruel</p>
    <p>World</p>
  </body>
</html>
XML;

require_once('../vendor/autoload.php');

echo FluentDOM($xml)
  ->find('//p')
  ->wrap('<div class="outer"><div class="inner"></div></div>');
