<?php

namespace App\Http\Middleware;

use App\Models\Entity\User;
use Closure;
use Exception;
use IMiddleware;
use Luthier\Http\Request;
use Luthier\Http\Response;

/**
 * Classe middleware para verificar as permissões do usuário. Por padrão das informações lidas em "can"
 * devem estar presentes no payload como ["can" => "algo"]. Um exemplo seria verificar se o usuário pode ou não cadastrar produtos
 */
class Can implements IMiddleware
{

  public function handle(Request $request, Closure $next): Response
  {
    $roles = $request->roles();
    $userRoles = $request->getPayload("can");

    if (self::verify($roles, $userRoles)) return $next($request);

    throw new Exception("Usuário não tem permissão para fazer essa ação!", 401);
  }

  public static function verify(array $roles, array $userRoles)
  {
    foreach ($roles as $role) {
      return in_array($role, $userRoles);
    }
  }
}
