<?php

namespace Luthier\Http\Middlewares;

use App\Models\Entity\UserEntity;
use Closure;
use Exception;
use Luthier\Http\Middlewares\IMiddleware;
use Luthier\Http\Request;
use Luthier\Http\Response;

/**
 * Classe middleware para verificar as permissões do usuário. Por padrão das informações lidas em "can"
 * devem estar presentes no payload como ["can" => "algo"]. Um exemplo seria verificar se o usuário pode ou não cadastrar produtos
 */
class Can implements IMiddleware
{
  public function handle(Request $request, Response $response, Closure $next): Response
  {
    $requiredRules = $request->rules();
    $user          = $request->getUser();

    if(is_array($user)) throw new Exception("Usuário não está autenticado!", 401);

    if (self::verify($requiredRules, $user)) return $next($request, $response);

    throw new Exception("Usuário não tem permissão para fazer essa ação!", 401);
  }

  public static function verify(array $rules, UserEntity $user)
  {
    /** LÓGICA DE VERIFICAR REGRAS */
    $userRules = array_map(function ($role) {
      return $role['NAME'];
    }, $user->getRules());

    foreach ($rules as $role) {
      return in_array($role, $userRules);
    }
  }
}
