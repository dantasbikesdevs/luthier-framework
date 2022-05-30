<?php

namespace Luthier\Exceptions;

class ResponseException extends AppException
{
  public function __construct(string $message, int $code)
  {
    parent::__construct($message, $code);
  }
}
