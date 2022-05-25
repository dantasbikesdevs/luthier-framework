<?php

namespace Luthier\Http\Middlewares;

use Closure;
use Exception;
use Luthier\Http\Middlewares\IMiddleware;
use Throwable;
use Luthier\Http\Request;
use Luthier\Http\Response;
use Luthier\Security\Jwt as JwtService;

class Jwt implements IMiddleware
{

  public function handle(Request $request, Response $response, Closure $next): Response
  {
    $jwt = $request->getHeader("Authorization") ?? $request->getCookie("jwt") ?? "";

    try {
      $payload = JwtService::decode($jwt);
      $request->setPayload($payload);
    } catch (Throwable $error) {
      if (getenv("ENV") == "DEV") throw new Exception("Acesso não permitido. Stacktrace: $error.", 403);
      throw new Exception("Acesso não permitido.", 403);
    }

    return $next($request, $response);
  }
}
