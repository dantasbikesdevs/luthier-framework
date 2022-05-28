<?php

namespace Luthier\Http\Middlewares;

use App\Models\Entity\UserEntity;
use Exception;
use Closure;
use Luthier\Http\Middlewares\IMiddleware;
use Luthier\Http\Request;
use Luthier\Http\Response;

/**
 * Classe middleware para verificar as permissões do usuário. Por padrão das informações lidas em "Is"
 * devem estar presentes no payload como ["is" => "algo"]. Um exemplo seria verificar se o usuário é ou não administrador.
 */
class Is implements IMiddleware
{

  public function handle(Request $request, Response $response, Closure $next): Response
  {

    // Permissões exigidas na requisição
    $requiredPermissions = $request->permissions();
    $user                = $request->getUser();

    if (self::verify($requiredPermissions, $user)) return $next($request, $response);

    throw new Exception("Usuário não tem permissão para fazer essa ação!", 401);
  }

  public static function verify(array $permissions, UserEntity $user)
  {
    /** LÓGICA DE VERIFICAR REGRAS */
    $userPermissions = array_map(function ($permission) {
      return $permission['NAME'];
    }, $user->getPermissions());

    foreach($permissions as $permission) {
      return in_array($permission, $userPermissions);
    }
  }
}
