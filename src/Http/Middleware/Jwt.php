<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Luthier\Http\Middleware\IMiddleware;
use Throwable;
use Luthier\Http\Request;
use Luthier\Http\Response;
use Luthier\Security\Jwt as JwtService;

class JWT implements IMiddleware
{

  public function handle(Request $request, Response $response, Closure $next): Response
  {
    $cookieJwt =  $request->getCookie("jwt") ?? "";

    try {
      $payload = JwtService::decode($cookieJwt);
      $request->setPayload($payload);
    } catch (Throwable $error) {
      if (getenv("ENV") == "DEV") throw new Exception("Acesso não permitido. Stacktrace: $error.", 403);
      throw new Exception("Acesso não permitido.", 403);
    }

    return $next($request, $response);
  }
}
