<?php

use Luthier\Environment\Environment;

it("cria variáveis de ambiente a partir de arquivo", function () {
  $env = new Environment(__DIR__ . "/../Fake/Env/.env");
  $env->load();

  expect(getenv("DEV"))->toBe("TRUE");
});
