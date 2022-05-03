<?php

use Luthier\Security\Jwt;

beforeAll(function () {
  $signature = '@A9c#h$o19AVsY0181kcm12asiw@ak198';
  Jwt::config($signature);
});

test("gera um jwt válido com payload e assinatura personalizadas", function () {
  $payload = [
    "id" => 1,
    "name" => "lorem",
    "permission" => "basic"
  ];

  $jwt = Jwt::encode($payload);
  // Gerado por https://jwt.io/#debugger-io
  $fakeJwt = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MSwibmFtZSI6ImxvcmVtIiwicGVybWlzc2lvbiI6ImJhc2ljIn0.h4GcPIRcIIi5RGZwNe47PcB35YVooxoeIRimHl4oPNQ";
  expect($jwt)->toEqual($fakeJwt);
});


test("valida o jwt retornando o payload desejado", function () {
  $payload = [
    "id" => 1,
    "name" => "lorem",
    "permission" => "basic"
  ];

  $jwt = Jwt::encode($payload);
  $decodedPayload = Jwt::decode($jwt);
  expect($decodedPayload)->toBe($payload);
});
