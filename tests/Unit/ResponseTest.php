<?php

use Luthier\Http\Response;

test("deve criar um objeto de resposta corretamente", function () {
  $response = new Response("Teste realizado com sucesso.", 200);

  expect($response->getContent())
    ->toBe("Teste realizado com sucesso.");

  expect($response->getCode())
    ->toBe(200);

  expect($response->getContentType())
    ->toBe("application/json");
});

test("deve enviar uma resposta corretamente", function () {
  $response = new Response();

  $result = $response->send(["message" => "Teste realizado com sucesso."]);

  expect($result)
    ->getContent()->toBe(["message" => "Teste realizado com sucesso."])
    ->getCode()->toBe(200);
});

test("deve pular tratamento de HTMLSPECIALCHARS", function () {
  $response = new Response();
  $response->ignoreAttributesEncodeHtmlSpecialChars();
  $response->send("<script>alert('Ola Mundo')</script>");

  expect($response->getContent())
    ->toBe("<script>alert('Ola Mundo')</script>");
});

test("deve tratar HTMLSPECIALCHARS", function () {
  $response = new Response();
  $response->send("<script>alert('Ola Mundo')</script>");

  expect($response->getContent())
    ->toBe("&lt;script&gt;alert(&#039;Ola Mundo&#039;)&lt;/script&gt;");
});

test("deve ignorar um atributo e não tratar HTMLSPECIALCHARS", function () {
  $response = new Response();
  $response->ignoreAttributesEncodeHtmlSpecialChars(["html"]);

  $response->ok([
    "html" => "<script>alert('Ola Mundo')</script>",
    "teste" => "<script>alert('Ola Mundo')</script>"
  ]);

  expect($response->getContent())
    ->toBe([
      "html" => "<script>alert('Ola Mundo')</script>",
      "teste" => "&lt;script&gt;alert(&#039;Ola Mundo&#039;)&lt;/script&gt;"
    ]);
});

test("deve ignorar e não tratar HTMLSPECIALCHARS", function () {
  $response = new Response();
  $response->ignoreAttributesEncodeHtmlSpecialChars();

  $response->ok([
    "html" => "<script>alert('Ola Mundo')</script>",
    "teste" => "<script>alert('Ola Mundo')</script>"
  ]);

  expect($response->getContent())
    ->toBe([
      "html" => "<script>alert('Ola Mundo')</script>",
      "teste" => "<script>alert('Ola Mundo')</script>"
    ]);
});

test("deve ignorar e não tratar HTMLSPECIALCHARS com contentType sendo text/html", function () {
  $response = new Response();
  $response->ignoreAttributesEncodeHtmlSpecialChars();

  $response->asHtml()->ok("<h1> Olá mundo </h1>");

  expect($response->getContent())
    ->toBe("<h1> Olá mundo </h1>");
});
