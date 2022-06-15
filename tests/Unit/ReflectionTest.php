<?php

use Luthier\Http\Response;
use Luthier\Reflection\Reflection;

class UserTest {
  private readonly int $ID;
  private string $NOME;
  private string $EMAIL;
  private string $SENHA;
  private string $SITUACAO;
  private array $ACOES;

  /**
   * Atributo que define quais atributos do modelo serão visíveis ao gerar SQL
   * de INSERT ou UPDATE).
   */
  private $unusedInSQL      = ["ID", "ACOES"];

  /**
   * Atributo que define quais atributos do modelo não serão visíveis ao usuário.
   */
  private $hiddenAttributes = ["SENHA", "ACOES"];

  public function __construct(int $id, string $name, string $email, string $password, string $situation) {
    $this->ID = $id;
    $this->NOME = $name;
    $this->EMAIL = $email;
    $this->SENHA = $password;
    $this->SITUACAO = $situation;
    $this->ACOES = [];
  }
}

beforeEach(function() {
  $this->user = new UserTest(1, "João", "joao.souza@gmail.com", "123456", "A");
});

test("deve retornar array com todos os atributos privados da classe", function () {
  $reflection = Reflection::getValuesObject($this->user);

  expect($reflection)
    ->toBeArray()
    ->not->toBeEmpty();

  expect($reflection)
    ->toHaveKeys(["unusedInSQL", "hiddenAttributes", "ID", "NOME", "EMAIL", "SENHA", "SITUACAO", "ACOES"]);
});

test("deve retornar array sem os atributos hiddenAttributes, unusedInSQL e os que constam no mesmo", function () {
  $reflection = Reflection::getValuesObjectToSQL($this->user);

  expect($reflection)
    ->toBeArray()
    ->not->toBeEmpty();

  expect($reflection)
    ->not->toHaveKeys(["unusedInSQL", "hiddenAttributes", "ID", "ACOES"]);
});

test("deve retornar array sem os atributos unusedInSQL, hiddenAttributes e os que constam no mesmo", function () {
  $reflection = Reflection::getValuesObjectToReturnUser($this->user);

  expect($reflection)
    ->toBeArray()
    ->not->toBeEmpty();

  expect($reflection)
    ->not->toHaveKeys(["unusedInSQL", "hiddenAttributes", "SENHA", "ACOES"]);
});
