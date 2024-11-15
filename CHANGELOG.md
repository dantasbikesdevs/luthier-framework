## Notas de atualização

### v0.1.0

- Classe de Request portada do Host das Financeiras
- Classe de Response portada do Host das Financeiras
- Classe de Middleware Queue portada do Host das Financeiras
- Classe de Fetch portada do Host das Financeiras
- Classe de XML Parser portada do Host das Financeiras
- Classe de Router portada do Host das Financeiras
- Classe de Utilidade Path portada do Host das Financeiras
- Classe de Utilidade Environment portada do Host das Financeiras
- Classe de Pagination portada do Host das Financeiras
- Classe de ConfigDatabase portada do Host das Financeiras
- Classe de Database portada do Host das Financeiras

### v1.0.0

- Template de API
- Classe de Reflection para visualização de atributos privados
- Carregamento automático de templates e middlewares

### v1.0.1

- Classe para renderização de conteúdo HTML
- Novo método na classe Query (filterWhere) que recebe um array com filtros para a consulta

### v1.0.2

- Novo método na classe Paginação que retorno se a página atual informada pelo cliente excede o total de páginas.

### V1.1.2
- Correção do REGEX responsável por tratar e extrair os valores entre os pipes (|) nas queries SQL

### v1.1.3
- Alteração do manipulador de exceções do sistema
- Retorno da query SQL responsável por causar exceção no PDO juntamente a mensagem de erro (Jamais permitir que ela seja exposta para o cliente).

### v2.0.0
- Alteração do queryBuilder. Foi removido o tratamento de parâmetros através dos pipes e/ou colchetes
por não serem tão confiáveis. Agora é através de um array de parâmetro e eles podem ser setados pelos métodos setParam ou setParams.
- Caso seja passado uma condição vazia como parâmetro nos métodos que setam condições a query (where, andWhere e orWhere), será lançado uma exceção do tipo QueryException.
- Nova atributo para indicar se a resposta deve ser convertida para entidades HTML.
- Nova interface para modelos que devem ser tratados pelos métodos de Reflection ao serem retornados para o usuário.

### v2.0.1
- Corrige problema ao inserir/atualizar campos que possuem caracteres especiais no nome da coluna.
- Corrige problema ao realizar filtros com alias de tabela e/ou com caracteres especiais no
nome da coluna.

### v2.1.0
- Método para geração de UUID v4.

### v3.0.0
- Refatorado todo o módulo de rotas. Nesta refatoração é corrigida problemas de
conflitos de rotas estáticas e dinâmicas, executação desnecessaria todas as rotas
e novas features.

### V3.0.1
- Refatoração dos métodos de autorização de rotas. Agora não é mais setado automaticamente o
middleware `auth`, o que deixava o sistema de autorização totalmente acoplado a este middleware.
Dessa forma, agora é necessário definir o middleware de autenticação manualmente com o método `middlewares()`
nas rotas que necessitam de autorização.
- Criação de uma classe de coleção para os middlewares das rotas.
- Ordem de execução dos middlewares alterada. Agora os middlewares globais (do grupo de rotas) são inseridos no
início da fila de execução, sendo assim também para os middlewares de rotas separadas. A ordem é definida pela
ordem de declaração no array de middlewares. Exemplo: ["auth", "cors"]. Primeiro é executado o middleware `auth` e depois o `cors`.
Já os middlewares de autorização são inseridos no final da fila de execução.

### V3.1.0

- Adiciona suporte a múltiplos parâmetros iguais na query string.
Exemplo: `?categoria=1&categoria=2&categoria=3&name=foo`

Retorno: `["categoria" => [1, 2, 3], "name" => "foo"]`
