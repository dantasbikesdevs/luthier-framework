<?php

namespace Luthier\Security;

use Exception;

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
  public static function matches(string $password, string $hashedPassword)
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
}
