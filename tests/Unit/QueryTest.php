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
    ->where("u.ID = [$id]")
    ->orWhere("u.NAME = [$username]");

  $statement = $query->getSql();

  expect($statement["query"])
    ->toBe("SELECT * FROM USERS u INNER JOIN USERS_GROUPS ug ON u.ID = ug.USER_ID WHERE u.ID = ? OR u.NAME = ?");
})->skip();

test("deve retornar uma query SQL com os filtros passados", function () {
  $id = 1;
  $name = "André Oliveira";
  $age = 19;

  $filters = [
    "ID <> [$id]",
    "NAME" => $name,
    "AGE > [$age]"
  ];

  $query = $this->queryBuilder->select("*")
    ->from("USERS u")
    ->filterWhere($filters);

  $statement = $query->getSql();

  expect($statement["query"])
    ->toBe("SELECT * FROM USERS u WHERE (ID <> ? AND NAME = ? AND AGE > ?)");

  expect($statement["values"])
    ->toBe(["1", "André Oliveira", "19"]);
})->skip();

test("deve lidar corretamente com queries onde existam colchetes nos parametros", function () {
  $id = 1;
  $name = "André [Oliveira][[]]][";
  $age = 20;
  $gender = "M[";

  $filters = [
    ["ID", $id, "<>"],
    ["NAME", $name],
    ["AGE", $age, "<"],
    ["GENDER", $gender]
  ];

  $query = $this->queryBuilder->select("*")
    ->from("USERS u")
    ->filterWhere($filters);

  $statement = $query->getSql();

  echo '<pre>';
  print_r($statement);
  echo '<br>';
  echo '</pre>';exit;

  expect($statement["query"])
    ->toBe("SELECT * FROM USERS u WHERE (ID <> ? AND NAME = ? AND AGE < ? AND GENDER = ?)");

  expect($statement["values"])
    ->toBe(["1", "André [Oliveira][[]]][", "20", "M["]);
});
