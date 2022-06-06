<?php

use App\Models\Entity\UserEntity;
use App\Repositories\UserRepository;

beforeAll(function () {
  loadDatabase();
});

beforeEach(function () {
  $this->repository = new UserRepository();
});

test('retorna usuário pelo id', function () {
  $user = $this->repository->findOne(1);

  expect($user)
    ->toBeObject()
    ->toBeInstanceOf(UserEntity::class)
    ->ID->toBe(1);
});

test('retorna usuário pelo e-mail', function () {
  $user = $this->repository->findOneByEmail("andreoliveira@gmail.com");

  expect($user)
    ->toBeObject()
    ->toBeInstanceOf(UserEntity::class)
    ->EMAIL->toBe("andreoliveira@gmail.com");
});

test('retorna todos os usuários', function () {
  $users = $this->repository->findAll();

  expect($users)
    ->toBeArray()
    ->not->toBeEmpty();
});

test('retorna usuário atráves payload JWT', function () {
  $payload = [
    "id" => 1,
    "name" => "Dantas"
  ];

  $user = $this->repository->getUserJWT($payload);

  expect($user)
    ->toBeObject()
    ->toBeInstanceOf(UserEntity::class)
    ->not->toBeEmpty();
});
