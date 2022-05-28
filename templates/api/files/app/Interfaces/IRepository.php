<?php

namespace App\Interfaces;


interface IRepository
{
  public function findOne(int $id);
  public function findAll();
  public function create($model);
  public function update($model, int $id);
  public function destroy(int $id);
}
