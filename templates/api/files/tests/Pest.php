<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

// uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

use App\Database\ApplicationDatabase;
use Luthier\Database\DatabaseManager;
use Luthier\Environment\Environment;

expect()->extend('toBeOne', function () {
  return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function loadEnv()
{
  $env = new Environment(__DIR__ . "/Fake/Env/.env");
  $env->load();
}

function loadDatabase()
{
  loadEnv();

  $data = [
    "driver"   => getenv('DEV_DRIVER'),
    "host"     => getenv('DEV_DB_HOST'),
    "path"     => getenv('DEV_DB_PATH'),
    "user"     => getenv('DEV_DB_USER'),
    "password" => getenv('DEV_DB_PASSWORD')
  ];
  $databaseManager = new DatabaseManager($data);
  ApplicationDatabase::init($databaseManager);
}
