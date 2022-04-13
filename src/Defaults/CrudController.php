<?php

namespace Luthier\Defaults;

use Luthier\Http\Request;
use Luthier\Http\Response;

abstract class CrudController
{
  public function create(Request $request, Response $response): Response
  {
    return $response;
  }

  public function createMany(Request $request, Response $response): Response
  {
    return $response;
  }

  public function get(Request $request, Response $response, int $id): Response
  {
    return $response;
  }

  public function getMany(Request $request, Response $response): Response
  {
    return $response;
  }

  public function update(Request $request, Response $response, int $id): Response
  {
    return $response;
  }

  public function updateMany(Request $request, Response $response, array $ids): Response
  {
    return $response;
  }

  public function delete(Request $request, Response $response, int $id): Response
  {
    return $response;
  }

  public function deleteMany(Request $request, Response $response, array $ids): Response
  {
    return $response;
  }
}
