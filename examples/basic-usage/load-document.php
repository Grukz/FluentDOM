<?php

require('../../src/FluentDOM.php');

$xmlFile = 'hello.xml';

// create object
$fd = FluentDOM::Query();
// use document attribute
$fd->document->load($xmlFile);

echo $fd
  ->find('/message')
  ->text('Hello World!');