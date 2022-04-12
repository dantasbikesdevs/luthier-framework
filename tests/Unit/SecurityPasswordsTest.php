<?php

use Luthier\Security\Password;

test("faz o hash de uma string e valida esse hash contra a string original", function () {
  $pass = "@SenhaMuitoComplexa10293847";

  $hash = Password::createHash($pass);

  $this->assertTrue(Password::verifyHash($pass, $hash));
});
