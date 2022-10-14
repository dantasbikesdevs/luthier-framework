<?php

use Luthier\Database\Database;
use Luthier\Database\Query;

beforeEach(function () {
  $stub = $this->createStub(Database::class);
  $this->queryBuilder = new Query($stub);
});

test("deve retornar uma query para consulta no banco", function () {
  $id = 1;
  $username = "João";

  $query = $this->queryBuilder->select("*")
    ->from("USERS u")
    ->innerJoinWith("USERS_GROUPS ug", "u.ID = ug.USER_ID")
    ->where("u.ID = :id")
    ->orWhere("u.NAME = :username")
    ->setParam(":id", $id)
    ->setParam(":username", $username);

  $statement = $query->getSql();

  expect($statement["query"])
    ->toBe("SELECT * FROM USERS u INNER JOIN USERS_GROUPS ug ON u.ID = ug.USER_ID WHERE u.ID = :id OR u.NAME = :username");
});

test("deve retornar uma query SQL com os filtros passados", function () {
  $id = 1;
  $name = "André Oliveira";
  $age = 19;

  $filters = [
    ["ID <> :id", ["id" => $id]],
    "NAME" => $name,
    ["AGE > :age", [$age]],
  ];

  $query = $this->queryBuilder->select("*")
    ->from("USERS u")
    ->filterWhere($filters);

  $statement = $query->getSql();

  expect($statement["query"])
    ->toBe("SELECT * FROM USERS u WHERE (ID <> :id AND NAME = :NAME AND AGE > :age)");

  expect($statement["values"])
    ->toBe(["id" => 1, "NAME" => "André Oliveira", 19]);
});

test("deve inserir em um campo o valor zero com sucesso", function () {
  $query = $this->queryBuilder->insert([
    "NAME" => "André",
    "AGE" => 0
  ], "USERS u")
    ->getSql();

    expect($query["query"])
      ->toBe("INSERT INTO USERS u (NAME,AGE) VALUES (:NAME,:AGE)");

    expect($query["values"])
      ->toBe(["NAME" => "André", "AGE" => 0]);
});

test("deve inserir um valor booleano com sucesso", function () {
  $query = $this->queryBuilder->insert([
    "NAME" => "André",
    "ACTIVE" => true
  ], "USERS u")
    ->getSql();

    expect($query["query"])
      ->toBe("INSERT INTO USERS u (NAME,ACTIVE) VALUES (:NAME,:ACTIVE)");

    expect($query["values"])
      ->toBe(["NAME" => "André", "ACTIVE" => true]);
});

test("deve atualizar um campo com o valor zero (0) com sucesso", function () {
  $query = $this->queryBuilder->update([
    "NAME" => "André",
    "AGE" => 0
  ], "USERS u")
    ->getSql();

    expect($query["query"])
      ->toBe("UPDATE USERS u SET NAME = :NAME,AGE = :AGE");

    expect($query["values"])
      ->toBe(["NAME" => "André", "AGE" => 0]);
});

test("deve atualizar um campo com o valor nulo com sucesso", function () {
  $query = $this->queryBuilder->update([
    "NAME" => "André",
    "TELEFONESECUNDARIO" => null
  ], "USERS u")
    ->getSql();

    expect($query["query"])
      ->toBe("UPDATE USERS u SET NAME = :NAME,TELEFONESECUNDARIO = :TELEFONESECUNDARIO");

    expect($query["values"])
      ->toBe(["NAME" => "André", "TELEFONESECUNDARIO" => null]);
});

test("deve atualizar um campo com o valor booleano com sucesso", function () {
  $query = $this->queryBuilder->update([
    "NAME" => "André",
    "ACTIVE" => false
  ], "USERS u")
    ->getSql();

    expect($query["query"])
      ->toBe("UPDATE USERS u SET NAME = :NAME,ACTIVE = :ACTIVE");

    expect($query["values"])
      ->toBe(["NAME" => "André", "ACTIVE" => false]);
});

test("deve filtrar um campo com o valor zero com sucesso", function () {
  $query = $this->queryBuilder->select("*")
    ->from("USERS u")
    ->filterWhere([
      "AGE" => 0
    ])
    ->getSql();

    expect($query["query"])
      ->toBe("SELECT * FROM USERS u WHERE (AGE = :AGE)");

    expect($query["values"])
      ->toBe(["AGE" => 0]);
});

test("deve adicionar um parâmetro a query corretamente com o setParam", function () {
  $query = $this->queryBuilder->select("*")
    ->from("USERS u")
    ->where("u.ID = :id")
    ->setParam("id", 1)
    ->getSql();

    expect($query["query"])
      ->toBe("SELECT * FROM USERS u WHERE u.ID = :id");

    expect($query["values"])
      ->toBe(["id" => 1]);
});

test("deve adicionar parâmetros a query corretamente com o setParams", function () {
  $query = $this->queryBuilder->select("*")
    ->from("USERS u")
    ->where("u.ID = :id")
    ->orWhere("u.NAME = :name")
    ->setParams(["id" => 1, "name" => "André"])
    ->getSql();

    expect($query["query"])
      ->toBe("SELECT * FROM USERS u WHERE u.ID = :id OR u.NAME = :name");

    expect($query["values"])
      ->toBe(["id" => 1, "name" => "André"]);
});
