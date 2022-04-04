<?php

namespace Luthier\Http\Middleware;

use Exception;
use Closure;
use IMiddleware;
use Luthier\Http\Request;
use Luthier\Http\Response;

/**
 * Classe middleware para verificar as permissões do usuário. Por padrão das informações lidas em "Is"
 * devem estar presentes no payload como ["is" => "algo"]. Um exemplo seria verificar se o usuário é ou não administrador.
 */
class Is implements IMiddleware
{

  public function handle(Request $request, Closure $next): Response
  {

    // Permissões exigidas na requisição
    $requiredPermissions = $request->permissions();

    // Tenta encontrar um campo de permissão no payload do usuário
    $userPermissions = $request->getPayload("is");

    if (self::verify($requiredPermissions, $userPermissions)) return $next($request);

    throw new Exception("Usuário não tem permissão para fazer essa ação!", 401);
  }

  public static function verify(array $permissions, array $userPermissions)
  {
    foreach ($permissions as $permission) {
      return in_array($permission, $userPermissions);
    }
  }
}
