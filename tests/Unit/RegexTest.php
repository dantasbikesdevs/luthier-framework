<?php

use Luthier\Regex\Regex;

test("verifica se a assinatura é muito fraca", function ($signature) {
  $result = preg_match(pattern: Regex::$strongSignature, subject: $signature);
  expect($result)->toBe(0);
})->with([
  "assinaturas"
]);

test("verifica se a senha é muito fraca", function ($password) {
  $result = preg_match(pattern: Regex::$strongPassword, subject: $password);
  expect($result)->toBe(0);
})->with([
  "senhas"
]);
