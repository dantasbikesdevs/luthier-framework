<?php

namespace Luthier\Security;

use App\Models\Entity\UserEntity;
use App\Repositories\UserRepository;
use Luthier\Exceptions\AppException;

class Auth {
  public static function authJWT($payload) {
    $user = (new UserRepository)->getUserJWT($payload);
    if(!$user instanceof UserEntity) throw new AppException("Token inválido.", 404);

    return $user;
  }
}
