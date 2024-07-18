<?php

declare(strict_types=1);

namespace Luthier\Xml;

use DOMDocument;
use SimpleXMLElement;

class XmlParser
{
  /**
   * Recebe um XML e retorna um SimpleXMLElement com estes elementos.
   */
  public static function toSimpleXMLElement(string $xml): SimpleXMLElement
  {
    // Usa to array para preserver os dados em CDATA antes de converter para SimpleXMLElement
    $xmlAsArray = self::toArray($xml);
    $xmlAsSimpleXMLElement = simplexml_load_string(self::xmlEncode($xmlAsArray, "UTF-8"));

    return $xmlAsSimpleXMLElement;
  }

  /**
   * Recebe um XML e retorna um array com estes elementos.
   */
  public static function toArray(string|SimpleXMLElement $xml): array
  {
    if ($xml instanceof SimpleXMLElement) {
      $xml = self::xmlEncode($xml, "UTF-8");
    }
    $xmlAsArray = json_decode(self::toJson($xml, true), true);

    return $xmlAsArray;
  }

  /**
   * Recebe um XML e retorna um Json com tais elementos (Sempre em letras minusculas).
   */
  public static function toJson(string $xml, bool $preserveRoot): string
  {
    // Transforma as tags encodadas em seus símbolos outra vez
    $decodedXml = trim(html_entity_decode($xml));

    if (empty($decodedXml)) {
      return [];
    }

    /**
     * O regex a seguir encontra as tags XML que possuem dois pontos ":" em sua composição.
     * Isso é necessário pois esses dois pontos atrapalham o parsing para array.
     */
    $xmlWithoutNamespaces = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $decodedXml);
    $xmlElement = new SimpleXMLElement($xmlWithoutNamespaces, LIBXML_NOCDATA);

    $xmlAsArray = (array) $xmlElement;

    if ($preserveRoot) {
      $xmlAsArray = [
        $xmlElement->getName() => [
          ...$xmlAsArray,
        ],
      ];
    }

    // Pega o XML e passa para array, aí pega o array e passa para json
    $xmlAsJson = json_encode($xmlAsArray);

    return $xmlAsJson;
  }

  /**
   * Inclui headers e propriedades em elementos XML que estão sendo gerados a partir de arrays
   * Para incluir uma propriedade no elemento XML a partir de um array use:
   * [
   *      "elemento" => [
   *           "Attr_NomeDaPropriedade" => "valor",
   *           "elemento_filho" => "..."
   *      ]
   * ].
   *
   * @return string|bool
   */
  public static function xmlEncode($mixed, $utf, $header = true, $domElement = null, $DOMDocument = null)
  {
    $attr = "Attr_";
    // Cria o objeto
    if (is_null($DOMDocument)) {
      $DOMDocument = new DOMDocument("1.0", $utf);
      $DOMDocument->formatOutput = true;
      $data = $mixed;
      if ($mixed instanceof SimpleXMLElement) {
        $data = [$mixed->getName() => (array) $mixed];
      }
      self::xmlEncode($data, $utf, $header, $DOMDocument, $DOMDocument);
      // Retira a declaração do header do XML $header = 'false'
      return ($header) ? $DOMDocument->saveXML() : $DOMDocument->saveXML($DOMDocument->documentElement);
    }
    // Popula o XML
    foreach ($mixed as $index => $mixedElement) {
      if (is_array($mixedElement)) {
        /*
         * Dado um array associativo com filhos de índice numérico retornamos
         * vários elementos xml com o mesmo nome da chave pai. Isto é necessário
         * porque não é possível ter várias chaves com o mesmo nome.
         *  */
        if (is_int($index)) {
          if ($index == 0) {
            $node = $domElement;
          } else {
            $node = $DOMDocument->createElement($domElement->tagName);

            $domElement->parentNode->appendChild($node);
          }
        } else {
          $plural = $DOMDocument->createElement($index);
          $domElement->appendChild($plural);
          $node = $plural;

          if (rtrim($index, '') !== $index) {
            $singular = $DOMDocument->createElement(rtrim($index, ''));
            $plural->appendChild($singular);
            $node = $singular;
          }
        }
        self::xmlEncode($mixedElement, $utf, $header, $node, $DOMDocument);
      } else {
        if (strpos($index, $attr) !== false) {
          $plural = $DOMDocument->createElement($index);
          $domElement->setAttribute(substr($index, strlen($attr)), (string) $mixedElement);
        } else {
          if (is_int($index)) {
            if ($index == 0) {
              $node = $domElement;
            } else {
              $node = $DOMDocument->createElement($domElement->tagName, $mixedElement);
              $domElement->parentNode->appendChild($node);
            }
          } else {
            $plural = $DOMDocument->createElement($index, (string) $mixedElement);
            $domElement->appendChild($plural);
          }
        }
      }
    }
  }
}
