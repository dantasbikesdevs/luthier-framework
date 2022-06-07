<?php

use Luthier\Database\Database;
use Luthier\Database\Query;

test("deve retornar uma query para consulta no banco", function () {
  $stub = $this->createStub(Database::class);

  $queryBuilder = new Query($stub);

  $id = 1;
  $username = "João";

  $query = $queryBuilder->select("*")
    ->from("USERS u")
    ->innerJoinWith("USERS_GROUPS ug", "u.ID = ug.USER_ID")
    ->where("u.ID = |$id|")
    ->orWhere("u.NAME = |$username|");

  $statement = $query->getSql();

  expect($statement["query"])
    ->toBe("SELECT * FROM USERS u INNER JOIN USERS_GROUPS ug ON u.ID = ug.USER_ID WHERE u.ID = ? OR u.NAME = ?");
});
