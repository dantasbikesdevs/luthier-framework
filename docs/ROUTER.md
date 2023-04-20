# ROUTER


O roteador é o ponto de entrada para o aplicativo. É responsável por
requisições de roteamento para o controlador e ação apropriados.

## Inicialização

Antes de tudo, você precisa inicializar o roteador no ponto inicial da aplicação `/public/index.php`.
No index.php, você deve inicializar o roteador e executar o método `run()`. Este método retornará
como retorno uma instância de `Luthier\Http\Response`. Para enviar a resposta para o usuário, você deve utilizar
o método `sendResponses()`.

Exemplo:

```php
<?php

use Luthier\Http\Router\Router;

$router = new Router();

$router->run()
    ->sendResponses();
```

## Definindo rotas

A definição de rotas deve ser feita entre a inicialização do roteador e a execução do método `run()`.
Isso porque os atributos da classe são inicializados no construtor da classe.

A classe Roter possui 6 métodos estáticos para definição de rotas: `get()`, `post()`, `put()`, `patch()`, `delete()` e `prefix()`.

Todos os métodos, com exceção do `prefix()`, recebem 2 parâmetros: a URI e a ação da rota. Esta ação pode ser de 3 tipos diferentes:

- Closure (função anônima) - contém uma função anônima que será executada quando a rota for chamada.

Exemplo:

```php
<?php

Router::get("/rota", function () {
    return "Olá mundo!";
});
```

- Array - contém a classe do controlador e o nome do método do controlador.

Exemplo:

```php
<?php

Router::get("/rota", [Controller::class, "metodoDoControlador"]);
```

- String - contém o nome do método do controlador que foi definido para o grupo ou posteriomente definido através do método `controller()`.

Exemplo:

```php
<?php

Router::get("/rota", "metodoDoControlador")
    ->controller(Controller::class);
```
Após essa primeira definição, os métodos retornam uma instância de `Luthier\Http\Router\Route` com métodos que podem ser utilizados para
demais modificações na rota.

### Rotas dinâmicas

Rotas dinâmicas são rotas que recebem parâmetros. Estes parâmetros são definidos entre chaves `{}`.

Para "coletar" o valor do parâmetro da URL, você deve definir um parâmetro na função ou método do controlador
com o mesmo nome. Dessa forma, o valor do parâmetro será passado para o parâmetro da função/método
automaticamente.

Exemplo:

```php
<?php

Router::get("/rota/{parametro}", function ($parametro) {
    return "Olá mundo! O parâmetro é: {$parametro}";
});
```

Lembrando que rotas estáticas têm prioridade sobre rotas dinâmicas. Ou seja, se você definir uma rota estática
para `/rota/parametro` e uma rota dinâmica para `/rota/{parametro}`, e for realizada uma requisição para `/rota/parametro`,
a rota estática será chamada se as mesmas possuírem o mesmo método HTTP.

Além disso, caso o parâmetro da função/método seja tipado com algum tipo númerico primitivo (int ou float) e o parâmetro
da URL não o for, uma exceção do tipo `Luthier\Exceptions\ParameterException` será lançada.

### Grupo de rotas

O método `prefix()` é utilizado para definir um prefixo para o grupo de rotas definidas no método `group()`.

Exemplo de grupo de rotas:

```php
<?php

Router::prefix("/users")->group([
    Router::get("/", function () {
        return "Olá mundo!";
    }),

    Router::get("/{id}", function ($id) {
        return "O parâmetro é: {$id}";
    })
]);
```

#### Grupo de rotas com o mesmo controlador

Você pode definir um controlador para um grupo de rotas.
Para isso, você deve utilizar o método `controller()` e, em conjunto, definir o método do controlador
para cada rota.

Exemplo:

```php
<?php

Router::prefix("/users")
    ->controller(Controller::class)
    ->group([
    Router::get("/", "index"),

    Router::get("/{id}", "findOne"),

    Router::delete("/{id}", "delete")
]);
```

### Parâmetros padrões

Por padrão, além das variáveis da URL, caso existam, são passadas via parâmetro uma instância de `Luthier\Http\Request` e uma instância de `Luthier\Http\Response`.
Para "capturá-las", você deve definir os parâmetros na função/método do controlador com `$request` e `$response` sem ou com a mesma
tipagem das classes para que não ocorra erro.

### Middlewares

Middlewares são ações que são executadas antes da execução da rota. Estas funções podem ser utilizadas para
validações, autenticação, etc.

Para definir o(s) middleware(s) de uma rota, você precisa utilizar o método `middlewares()` e passar um array
com o nome dos mesmos.

Exemplo:

```php
<?php

Router::get("/rota", function () {
    return "Olá mundo!";
})->middlewares(["auth"]);
```

Lembre-se que o middleware deve estar registrado para que seja executado.

### Permissões

Para definir permissões para a rota, você pode utilizar os métodos `is()`, `can()` e `see()`.

- **Is**: método utilizado para setar as permissões com relação ao cargo do usuário.
O mesmo recebe um array de cargos.

Exemplo:

```php
<?php

Router::get("/rota", function () {
    return "Olá mundo!";
})->is(["admin"]);
```

- **Can**: método utilizado para setar as permissões com relação ao que o usuário pode fazer.

Exemplo:

```php
<?php

Router::get("/rota", function () {
    return "Olá mundo!";
})->can(["read_route"]);
```

- **See**: método utilizado para setar as permissões com relação a telas que o usuário pode ver.

Exemplo:

```php
<?php

Router::get("/rota", function () {
    return "Olá mundo!";
})->see(["marketing"]);
```

**Obs.:** Lembre-se de setar o middleware de autenticação que irá setar o usuário na requisição, pois os métodos
de autorização o utilização para realizar as validações.

Exemplo:

```php
<?php

Router::get("/rota", function () {
    return "Olá mundo!";
})->middlewares(["auth"])->see(["marketing"]);
```
