<?php

namespace Luthier\Security;

use Exception;
use Luthier\Regex\Regex;

class Password
{
  /**
   * Recebe uma senha e retorna seu conteúdo em um hash com salt
   */
  public static function createHash(string $password)
  {
    /**
     * OBS: Password hash é provavelmente a melhor forma de fazer isso atualmente
     * Cuidado ao trocar o algoritmo. Estamos usando o padrão e esse padrão pode mudar
     * com o tempo. Se isso acontecer é porque muito provavelmente encontraram um melhor
     * ou algo do tipo.
     *
     * O salt não deve ser gerado manualmente.
     *
     * O campo de senha no banco deve ter ao menos 255 caracteres, só para garantir.
     */
    if ($password) {
      return password_hash($password, PASSWORD_DEFAULT);
    }

    throw new Exception("Senha inválida. O conteúdo de senha não pode estar vazio.", 400);
  }

  /**
   * Recebe um hash de uma senha, uma senha em texto puro e verifica se elas são correspondentes
   */
  public static function verifyHash(string $password, string $hashedPassword)
  {
    /**
     * Talvez você se pergunte o porque de estarmos apenas encapsulando
     * uma função dentro de outra. Então é por isso que eu vou te explicar:
     *
     * Precisamos isolar o uso dessa função porque existe a chance de algum
     * dia por algum motivo alguém decidir trocar, atualizar ou depreciar essa função.
     * Com a chamada de algo tão critico centralizado aqui será mais fácil para adaptarmos
     * nosso programa para lidar com isso.
     *
     * O custo de performance não é tão grande assim, eu imagino, já que não estamos fazendo
     * nenhuma outra operação maluca.
     */
    return password_verify($password, $hashedPassword);
  }

  /**
   * Recebe uma senha para validar e um número mínimo de caráteres para ela.
   */
  public static function validate(string $password, int $minLength = 8)
  {
    if ($minLength < 8) throw new Exception("Impossível configurar um tamanho mínimo tão baixo. Insira um número maior ou igual a oito.", 500);

    if (strlen($password < $minLength) ||  !preg_match(Regex::$strongPassword, $password)) {
      throw new Exception("Senha inválida. Uma boa senha tem no mínimo $minLength caracteres, um caractere especial ($*&@#), um número e uma letra maiúscula.", 400);
    }
  }
}
