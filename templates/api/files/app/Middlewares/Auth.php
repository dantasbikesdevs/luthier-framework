<?php declare(strict_types=1);

namespace App\Middlewares;

use App\Models\Entity\UserEntity;
use App\Repositories\UserRepository;
use Closure;
use Exception;
use Luthier\Exceptions\AppException;
use Luthier\Http\Middlewares\IMiddleware;
use Throwable;
use Luthier\Http\Request;
use Luthier\Http\Response;
use Luthier\Security\Jwt as JwtService;

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

      $user = self::authJWT($payload);
      $request->setUser($user);
    } catch (Throwable $error) {
      throw new Exception("Acesso não permitido.", 403);
    }

    return $next($request, $response);
  }

  public static function authJWT($payload) {
    $user = (new UserRepository)->getUserJWT($payload);
    if(!$user instanceof UserEntity) throw new AppException("Token inválido.", 404);

    return $user;
  }
}
