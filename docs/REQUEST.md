# REQUEST

O objeto de `Request` é usado para obter informações da requisição feita por um cliente.

A partir de um objeto `Request` podemos obter coisas como:

- **ROTEADOR**

> Instância de Router

```php
$objetoDeRequest->getRouter();
```

- **MÉTODO HTTP**

> String com o método _(GET, PUT, POST, DELETE...)_

```php
$objetoDeRequest->getHttpMethod();
```

- **URI**

> String com o a URI de onde a requisição partiu

```php
$objetoDeRequest->getUri()

```

- **CABEÇALHOS HTTP**

> Array associativo com os cabeçalhos http da requisição num formato `["nome" => "conteúdo"]`

```php
$objetoDeRequest->getHeaders();
```

- **PARÂMETROS NA URL**

> Array associativo com os parâmetros passados pela URL _(query strings)_ em requisições do tipo GET:

URL: `https://site.com/caminho/para/pagina?parametro=valor&outroparametro=valor2`

ARRAY: `["parametro" => "valor", "outroparametro" => "valor2"]`

```php
$objetoDeRequest->getQueryParams();
```
