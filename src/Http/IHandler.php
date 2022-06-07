<?php

namespace Luthier\Http;

interface IHandler {
  public function getResponse(): Response;
}
