<?php declare(strict_types=1);

namespace App\Middlewares;

use Closure;
use Exception;
use Luthier\Http\Middlewares\IMiddleware;
use Throwable;
use Luthier\Http\Request;
use Luthier\Http\Response;
use Luthier\Security\Jwt as JwtService;

class JwtCookie implements IMiddleware
{

  public function handle(Request $request, Response $response, Closure $next): Response
  {
    $cookieJwtName = getenv("JWT_COOKIE_NAME");
    $cookieJwt =  $request->getCookie($cookieJwtName);
    $cookieJwt = !empty($cookieJwt) ? str_replace('Bearer ', '', $cookieJwt) : '';

    try {
      $payload = JwtService::decode($cookieJwt);
      $request->setPayload($payload);

      $user = Auth::authJWT($payload);
      $request->setUser($user);
    } catch (Throwable $error) {
      throw new Exception("Acesso não permitido.", 403);
    }

    return $next($request, $response);
  }
}
