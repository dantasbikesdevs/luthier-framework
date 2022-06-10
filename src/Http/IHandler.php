<?php declare(strict_types=1);

namespace Luthier\Http;

interface IHandler {
  public function getResponse(): Response;
}
