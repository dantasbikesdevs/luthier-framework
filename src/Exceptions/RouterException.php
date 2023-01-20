<?php

declare(strict_types=1);

namespace Luthier\Exceptions;

class RouterException extends LuthierException
{
  public function __construct(string $message = "", int $code = 0)
  {
    parent::__construct($message, $code);
  }
}
