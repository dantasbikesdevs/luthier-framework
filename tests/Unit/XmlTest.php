<?php

use Luthier\Xml\XmlParser;

use function PHPUnit\Framework\assertEquals;

test("passagem de xml para array associativo", function () {
  $xml = '
  <pai xmlns="http://www.pai.com.br/schemas/pai">
    <filho id="12">
      <neto>
        <idade>1</idade>
        <nome>Lorem</nome>
      </neto>
      <neto>
        <nome>Ipsum</nome>
      </neto>
    </filho>
    <nome>Dolor</nome>
  </pai>';

  $expectedArray = [
    "filho" => [
      "@attributes" => ["id" => "12"],
      "neto" => [
        ["idade" => 1, "nome" => "Lorem"],
        ["nome" => "Ipsum"],
      ],
    ],
    "nome" => "Dolor"
  ];

  $result = XmlParser::toArray($xml);

  assertEquals($expectedArray, $result);
});


test("tranformação de xml em json", function () {
  $xml = '
  <pai xmlns="http://www.pai.com.br/schemas/pai">
    <filho id="12">
      <neto>
        <idade>1</idade>
        <nome>Lorem</nome>
      </neto>
      <neto>
        <nome>Ipsum</nome>
      </neto>
    </filho>
    <nome>Dolor</nome>
  </pai>';

  $expectedJson = '{"filho":{"@attributes":{"id":"12"},"neto":[{"idade":"1","nome":"Lorem"},{"nome":"Ipsum"}]},"nome":"Dolor"}';

  $result = XmlParser::toJson($xml);

  assertEquals($expectedJson, $result);
});

test("transformação de array em xml", function () {
  $array = [
    "filho" => [
      "Attr_id" => 12,
      "neto" => [
        ["idade" => 1, "nome" => "Lorem"],
        ["nome" => "Ipsum"],
      ],
    ],
    "nome" => "Dolor"
  ];

  $expectedXml =
    '<?xml version="1.0" encoding="utf-8"?>
<filho id="12">
  <neto>
    <idade>1</idade>
    <nome>Lorem</nome>
  </neto>
  <neto>
    <nome>Ipsum</nome>
  </neto>
</filho>
<nome>Dolor</nome>
';

  $result = XmlParser::xmlEncode($array, "utf-8");

  assertEquals($expectedXml, $result);
});
