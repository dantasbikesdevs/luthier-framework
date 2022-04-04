<?php

use Luthier\Xml\XmlParser;

use function PHPUnit\Framework\assertEquals;

test("passagem de xml para array associativo", function () {
  $xml = "
    <pai xmlns=\"http://www.pai.com.br/schemas/pai\">
      <filho id=\"12\">
        <neto>
          <idade>1</idade>
          <nome>Lorem</nome>
        </neto>
        <neto>
          <nome>Ipsum</nome>
        </neto>
      </filho>
      <nome>Dolor</nome>
    </pai>";

  $resultadoEsperado = [
    "filho" => [
      "@attributes" => ["id" => "12"],
      "neto" => [
        ["idade" => 1, "nome" => "lorem"],
        ["nome" => "ipsum"],
      ],
    ],
    "nome" => "dolor"
  ];

  $resultado = XmlParser::toArray($xml);

  assertEquals($resultadoEsperado, $resultado);
});
