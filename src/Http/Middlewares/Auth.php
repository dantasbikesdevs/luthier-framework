<?php

namespace Luthier\Http\Middlewares;

use Closure;
use Exception;
use Luthier\Http\Middlewares\IMiddleware;
use Throwable;
use Luthier\Http\Request;
use Luthier\Http\Response;
use Luthier\Security\Jwt as JwtService;
use Luthier\Security\Auth as AuthService;

class Auth implements IMiddleware
{

  public function handle(Request $request, Response $response, Closure $next): Response
  {
    $cookieJwtName = getenv("JWT_COOKIE_NAME");
    $jwt =  $request->getCookie($cookieJwtName) ?? $request->getHeader("Authorization");
    $jwt = !empty($jwt) ? str_replace('Bearer ', '', $jwt) : '';

    try {
      $payload = JwtService::decode($jwt);
      $request->setPayload($payload);

      $user = AuthService::authJWT($payload);
      $request->setUser($user);
    } catch (Throwable $error) {
      throw new Exception("Acesso não permitido.", 403);
    }

    return $next($request, $response);
  }
}
