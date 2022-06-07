<?php

use Luthier\Regex\Regex;

test("verifica se a assinatura é muito fraca", function ($signature) {
  $this->assertDoesNotMatchRegularExpression(Regex::$strongSignature, $signature);
})->with([
  "broken_signatures"
]);

test("verifica se a senha é muito fraca", function ($password) {
  $this->assertDoesNotMatchRegularExpression(Regex::$strongPassword, $password);
})->with([
  "broken_passwords"
]);

test("verifica se os e-mails são válidos", function ($email) {
  $this->assertDoesNotMatchRegularExpression(Regex::$validEmail, $email);
})->with([
  "invalid_emails"
]);

test("verifica se há espaços duplicados na string", function () {
  $this->assertMatchesRegularExpression(Regex::$contiguousBlankSpaces, "a   b");
});
