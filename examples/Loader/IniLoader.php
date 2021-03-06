<?php

class IniLoader implements FluentDOM\Loadable {

  public function supports(string $contentType): bool {
    return in_array($contentType, ['ini', 'text/ini'], FALSE);
  }

  public function load($source, string $contentType = 'text/ini', $options = []) {
    if (is_string($source) && $this->supports($contentType)) {
      if (!file_exists($source)) {
        throw new InvalidArgumentException('File not found: '. $source);
      }
      if ($iniFile = parse_ini_file($source)) {
        $document = new FluentDOM\DOM\Document();
        $root = $document->appendChild($document->createElement('ini'));
        $this->_arrayToNodes($document, $root, $iniFile);
        return $document;
      }
    }
    return FALSE;
  }

  public function loadFragment($source, string $contentType = 'text/ini', $options = []) {
    throw new \FluentDOM\Exceptions\InvalidFragmentLoader(self::class);
  }

  private function _arrayToNodes(FluentDOM\DOM\Document $document, DOMNode $node, $data) {
    if (is_array($data)) {
      foreach ($data as $key => $val) {
        if (preg_match('(^\d+$)', $key)) {
          $nodeName = $node->nodeName;
          if (substr($nodeName, -1) === 's') {
            $nodeName = substr($nodeName, 0, -1);
          }
          $childNode = $document->createElement($nodeName);
          $this->_arrayToNodes($document, $childNode, $val);
          $node->appendChild($childNode);
        } elseif (is_array($val)) {
          $childNode = $document->createElement($key);
          $this->_arrayToNodes($document, $childNode, $val);
          $node->appendChild($childNode);
        } elseif (preg_match('([\r\n\t])', $val)) {
          $childNode = $document->createElement($key);
          $textNode = $document->createTextNode($val);
          $childNode->appendChild($textNode);
          $node->appendChild($childNode);
        } else {
          $node->appendChild($document->createElement($key, $val));
        }
      }
    } elseif (!empty($data)) {
      $textNode = $document->createTextNode($data);
      $node->appendChild($textNode);
    }
  }
}