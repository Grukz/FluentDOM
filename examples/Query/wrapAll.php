<?php
/**
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
* @copyright Copyright (c) 2009-2017 FluentDOM Contributors
*/
require __DIR__.'/../../vendor/autoload.php';

header('Content-type: text/plain');

$html = <<<HTML
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
HTML;

echo FluentDOM($html)
  ->find('//p')
  ->wrapAll('<div class="wrapper" />');

echo "\n\n";

echo FluentDOM($html)
  ->find('//p')
  ->wrapAll('<div class="wrapper"><div>INNER</div></div>');
