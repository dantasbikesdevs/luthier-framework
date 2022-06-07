<?php

use Luthier\Log\Log;

test("registra um log de erro", function () {
  $expection = new InvalidArgumentException("Exceção para teste de log de erro");
  $logger = new Log("main");

  $logger->error("Teste de erro", [
    "exception" => $expection
  ]);

  expect($logger->getLogs())->toBe([
    [
      "level" => "error",
      "message" => "Teste de erro",
      "context" => [
        "exception" => $expection
      ]
    ]
  ]);
});
