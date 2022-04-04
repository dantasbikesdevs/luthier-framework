<?php

use Luthier\JwtService\JwtService;

test("gera um jwt válido com payload e assinatura personalizadas", function () {
  $signature = "#A1s2d3f4GH@j1k2l3c4&1ka4mfdm434";

  $payload = [
    "id" => 1,
    "name" => "lorem",
    "permission" => "basic"
  ];

  $jwt = JwtService::encode($payload, $signature);

  // Gerado por https://jwt.io/#debugger-io
  $fakeJwt = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MSwibmFtZSI6ImxvcmVtIiwicGVybWlzc2lvbiI6ImJhc2ljIn0.uvfiYzsDfh3hxMAeLkfFJzsp_aVRjlm61YN16v2GeNU";

  expect($jwt)->toEqual($fakeJwt);
});


test("valida o jwt retornando o payload desejado", function () {
  $signature = "#A1s2d3f4GH@j1k2l3c4&1ka4mfdm434";

  $payload = [
    "id" => 1,
    "name" => "lorem",
    "permission" => "basic"
  ];

  $jwt = JwtService::encode($payload, $signature);

  $decodedPayload = JwtService::decode($jwt, $signature);

  expect($decodedPayload)->toBe($payload);
});
