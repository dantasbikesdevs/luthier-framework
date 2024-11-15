<?php declare(strict_types=1);

namespace App\Http\Middlewares;

use Closure;
use Exception;
use Luthier\Http\Middlewares\IMiddleware;
use Throwable;
use Luthier\Http\Request;
use Luthier\Http\Response;
use Luthier\Security\Jwt as JwtService;

class AuthCookie implements IMiddleware
{

  public function handle(Request $request, Response $response, Closure $next): Response
  {
    $headerJwt =  $request->getHeader("Authorization");
    $headerJwt = !empty($headerJwt) ? str_replace('Bearer ', '', $headerJwt) : '';

    try {
      $payload = JwtService::decode($headerJwt);
      $request->setPayload($payload);

      $user = Auth::authJWT($payload);
      Request::setUser($user);
    } catch (Throwable $error) {
      throw new Exception("Acesso não permitido.", 403);
    }

    return $next($request, $response);
  }
}
