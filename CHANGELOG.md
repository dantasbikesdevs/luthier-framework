# V0.1.0

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

# V1.0.0

- Template de API
- Classe de Reflection para visualização de atributos privados
- Carregamento automático de templates e middlewares

# V1.0.1

- Classe para renderização de conteúdo HTML
- Novo método na classe Query (filterWhere) que recebe um array com filtros para a consulta

# V1.0.2

- Novo método na classe Paginação que retorno se a página atual informada pelo cliente excede o total de páginas.

# V1.1.2
- Correção do REGEX responsável por tratar e extrair os valores entre os pipes (|) nas queries SQL

# V1.1.3
- Alteração do manipulador de exceções do sistema
- Retorno da query SQL responsável por causar exceção no PDO juntamente a mensagem de erro (Jamais permitir que ela seja exposta para o cliente).

# V2.0.0
- Alteração do queryBuilder. Foi removido o tratamento de parâmetros através dos pipes e/ou colchetes
por não serem tão confiáveis. Agora é através de um array de parâmetro e eles podem ser setados pelos métodos setParam ou setParams.
- Caso seja passado uma condição vazia como parâmetro nos métodos que setam condições a query (where, andWhere e orWhere), será lançado uma exceção do tipo QueryException.
- Nova atributo para indicar se a resposta deve ser convertida para entidades HTML.
- Nova interface para modelos que devem ser tratados pelos métodos de Reflection ao serem retornados para o usuário.

# V2.0.1
- Corrige problema ao inserir/atualizar campos que possuem caracteres especiais no nome da coluna.
- Corrige problema ao realizar filtros com alias de tabela e/ou com caracteres especiais no
nome da coluna.

# V2.1.0
- Método para geração de UUID v4.

# V3.0.0
- Refatorado todo o módulo de rotas. Nesta refatoração é corrigida problemas de
conflitos de rotas estáticas e dinâmicas, além de não executar todas as rotas desnecessariamente.
