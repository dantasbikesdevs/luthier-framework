<?php

namespace Luthier\Exceptions;

use Exception;

class ResponseException extends Exception
{
  public function __construct(string $message, int $code)
  {
    parent::__construct($message, $code);
  }
}
