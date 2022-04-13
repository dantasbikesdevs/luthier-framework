<?php

namespace Luthier\Defaults;

use Luthier\Http\Request;
use Luthier\Http\Response;

abstract class CrudController
{
  abstract public function create(Request $request, Response $response): Response;

  abstract public function createMany(Request $request, Response $response): Response;

  abstract public function get(Request $request, Response $response, int $id): Response;

  abstract public function getMany(Request $request, Response $response): Response;

  abstract public function update(Request $request, Response $response, int $id): Response;

  abstract public function updateMany(Request $request, Response $response): Response;

  abstract public function delete(Request $request, Response $response, int $id): Response;

  abstract public function deleteMany(Request $request, Response $response): Response;
}
