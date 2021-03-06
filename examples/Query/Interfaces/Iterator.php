<?php
require __DIR__.'/../../../vendor/autoload.php';

header('Content-type: text/plain');

$xml = <<<XML
<html>
  <head>
    <title>Examples: FluentDOM\Query Iterator interface</title>
  </head>
<body>
  <p>Hello</p>
  <p>cruel</p>
  <p>World</p>
</body>
</html>
XML;

foreach (FluentDOM($xml)->find('//p') as $key => $value) {
  /** @var \FluentDOM\DOM\Node $value */
  echo $key, ': ', $value, "\n";
}
