<?php

use Luthier\Log\Log;

beforeAll(function() {
  loadChannels();
});

test("registra um log de erro", function () {
  $expection = new InvalidArgumentException("Exceção para teste de log de erro");
  $logger = new Log("main");

  $logger->error("Teste de erro", [
    "exception" => $expection
  ]);

  expect($logger)
  ->toBeObject()
  ->toBeInstanceOf(Log::class);
});
