# LUTHIER FRAMEWORK

Este é um framework interno desenvolvido pelos integrantes e ex-integrantes da equipe web da Dantas Eletro, com o objetivo de facilitar o desenvolvimento de projetos internos da empresa. Por um momento de delírio momentâneo, foi cogitado a sua utilização no back-end de um dos projetos mais importantes, os e-commerces Dantas. Isso acabou se concretizando devido proibições vindas de alguns dos nossos superiores com relação a utilização de um framework PHP profissional como o Laravel, CodeIgniter, Symfony (apesar do Doctrine, ORM do Symfony, não ter um driver atualizado para o Firebird, até o momento (10/2022)).

## Entendendo a estrutura do Luthier

<br>

Pasta           | Objetivo
:---------------|:------------------------------------------------------------------------------------------------
**.vscode**     | É onde temos algumas configurações a fim de tentar padronizar o código-fonte.
**docs**        | É onde temos (ou deveriamos ter) a documentação dos módulos do framework.
**src**         | É onde temos o coração do framework com todos os seus módulos.
**templates**   | É onde temos alguns templates de examplos sistemas prontos utilizando o Luthier. Atualmente existe apenas um de API.
**tests**       | É onde temos testes de alguns módulos do framework.

Para melhor organização, as pastas iniciais seguem o padrão de estarem todas em **lowercase**, e suas subpastas com a primeira letra em maiusculo.

### Entendendo as subpastas

#### Subpastas da pasta src:

Pasta           | Objetivo
:---------------|:------------------------------------------------------------------------------------------------
**Cli**         | É onde temos os arquivos que cuidam do output para o cliente e instala o framework.
**Database**    | É onde temos o módulo de conexão com o banco de dados.
**Environment** | É onde temos o módulo que carrega os dados do arquivo .env como variáveis de ambiente.
**Exceptions**  | É onde temos as exceções personalizadas do framework.
**Http**        | É onde temos o módulo HTTP que trabalha com as rotas, requisições e respostas para os clientes.
**Reflection**  | É onde temos o módulo com métodos que utilizam a Reflection API do PHP para acessar atributos privados de qualquer classe.
**Security**    | É onde temos o módulo que trabalha com os tokens JWT e com a API de criptografia do PHP.
**Utils**       | É onde temos alguns classes utilitárias do framework.
**Xml**         | É onde temos o módulo de parser de XML para array PHP e o inverso.

## Instalação

Primeiramente, crie um arquivo composer.json no repositório do seu projeto com o seguinte conteúdo:

```json
{
  "name": "nome-projeto/project",
  "description": "Descrição do seu projeto",
  "autoload": {
    "psr-4": {
      "App\\": "app/"
    }
  },
  "scripts": {
    "dev": "cd public/; xdg-open 'http://127.0.0.1:8383'; php -S 127.0.0.1:8383",
    "test": "./vendor/bin/pest",
    "coverage": "./vendor/bin/pest --coverage"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "usuario@10.168.2.176:/srv/versionamento/git/web-luthier-frame-v1/.git"
    }
  ],
  "require": {
    "firebase/php-jwt": "^6.3.0",
    "dantas/luthier": "dev-main"
  },
  "require-dev": {
    "pestphp/pest": "^1.21"
  }
}
```

Após isso, inicie o script de instalação do Luthier:

```bash
$ php vendor/dantas/luthier/start.php
```

Selecione o template de API (ou outro de sua preferência) e prossiga com a instalação.

Após isso, será necessário instalar as dependências. Execute o seguinte comando na raiz do projeto:

```bash
$ composer install
```

Feito. Seja feliz!
