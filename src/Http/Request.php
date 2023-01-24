<?php

declare(strict_types=1);

namespace Luthier\Http;

use Luthier\Http\Router\Contracts\Router as RouterInterface;

class Request
{
    public const METHOD_OPTIONS = "OPTIONS";

    /**
     * Método HTTP da requisição
     */
    private string $httpMethod;

    /**
     * URI da página
     */
    private string $uri;

    /**
     * Tipo do conteúdo. Enviado como header Content Type
     */
    private string $contentType;

    /**
     * Variáveis recebidas por método HTTP GET ($_GET)
     */
    private array $queryParams = [];

    /**
     * Variáveis recebidas por método HTTP POST ($_POST)
     */
    private array $postVars = [];

    /**
     * Cookies da requisição
     */
    private array $cookies = [];

    /**
     * Informações do payload JWT
     */
    private array $payload = [];

    /**
     * Usuário autenticado da requisição
     */
    private static object|array|null $user = null;

    /**
     * Cabeçalhos da requisição
     */
    private array $headers = [];

    /**
     * Router da página
     */
    private RouterInterface $router;

    /**
     * Construtor da classe
     */
    public function __construct($router)
    {
        // Objeto da classe router que é injetado na requisição
        $this->router = $router;
        $this->headers = getallheaders();
        $this->httpMethod = $_SERVER['REQUEST_METHOD'] ?? '';
        $this->cookies = Cookie::getAll();
        $this->setUri();
        $this->setPostVars();
        $this->setQueryParams();
    }

    // ! API PÚBLICA

    /**
     * Método responsável por retornar o router
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * Método responsável por retornar o método HTTP da requisição
     */
    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    /**
     * Método responsável por retornar a uri da requisição
     */
    public function getUri(): string
    {
        $uri = preg_replace("/\/\/+/", "/", $this->uri);

        if (str_ends_with($uri, "/")) return $uri;

        return $uri . "/";
    }

    /**
     * Método responsável por retornar os Headers da requisição
     */
    public function getAllHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Método responsável por retornar os Headers da requisição
     */
    public function getHeader($header): ?string
    {
        return $this->headers[$header] ?? null;
    }

    /**
     * Método responsável por retornar os parâmetros da URL($_GET) da requisição
     */
    public function getAnyQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * Método responsável por retornar os parâmetros da URL($_GET) da requisição
     */
    public function getQueryParams(): array
    {
        $params = $this->queryParams;
        $validQueryParams = [];

        // Ex: ?nome=dev -> ["nome" => "dev"]
        foreach ($params as $name => $value) {
            $name = strtolower($name);

            if (!empty($value) || $value == 0) {
                $validQueryParams[$name] = $value;
            }
        }

        return $validQueryParams;
    }

    /**
     * Método responsável por retornar um parâmetro da URL($_GET) da requisição
     */
    public function getQueryParam(string $validParamName): string|int|bool|null
    {
        $params = $this->queryParams;

        if (isset($params[$validParamName])) {
            return $params[$validParamName];
        }

        return null;
    }

    /**
     *  Método responsável por retornar os parâmetros POST($_POST) da requisição
     */
    public function getPostVars($postValidated = null): array
    {
        if ($postValidated) {
            $this->validateParamsPost($postValidated);
        }
        return $this->postVars;
    }

    /**
     * Método responsável por retornar todos os cookies disponíveis
     */
    public function getAllCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Método responsável por retornar um cookies especifico
     */
    public function getCookie(string $cookieName): ?string
    {
        return $this->cookies[$cookieName] ?? null;
    }

    /**
     * Método responsável por definir informações do payload
     */
    public function setPayload(array $fields)
    {
        $this->payload = $fields;
    }

    /**
     * Método responsável por setar o usuário autenticado da requisição
     */
    public static function setUser($user): void
    {
        self::$user = $user;
    }

    /**
     * Método responsável por retornar o usuário autenticado da requisição
     */
    public static function getUser()
    {
        return self::$user;
    }

    /**
     * Método responsável por retornar informações do payload
     */
    public function getPayload(?string $field = null): mixed
    {
        if ($field) return $this->payload[$field];
        return $this->payload;
    }

    /**
     * Função responsável por pegar as permissões que o usuário deve possuir para acessar a rota
     */
    public function permissions()
    {
        return $this->router->getRoute()->getPermissions();
    }

    /**
     * Função responsável por pegar os papéis que o usuário deve possuir para acessar a rota
     */
    public function rules()
    {
        return $this->router->getRoute()->getRules();
    }

    /**
     * Função responsável por as telas que o usuário deve possuir para acessar a rota
     */
    public function screens()
    {
        return $this->router->getRoute()->getScreens();
    }

    /**
     * Método responsável por dizer que os parâmetros são obrigatórios
     */
    public function postRequired($postRequired): void
    {
        foreach ($postRequired as $key) {
            if (!$this->postVars[$key]) {
                throw new \Exception("O parâmetro ${key} é obrigatório!");
            }
        }
    }

    // ! MÉTODOS INTERNOS

    /**
     * Método responsável por definir as variáveis que vem no corpo da requisição
     */
    private function setPostVars()
    {
        $postVars = $_POST ?? [];
        $json = file_get_contents('php://input');
        $jsonVars = json_decode($json, true) ?? [];
        $postVars = array_merge($jsonVars, $postVars);
        $this->postVars = $this->sanitize($postVars);
    }

    /**
     * Método responsável por definir os parâmetros ($_GET) da URL
     */
    private function setQueryParams()
    {
        $queryParams = $_GET ?? [];
        $this->queryParams = $this->sanitize($queryParams);
    }

    /**
     * Método responsável por definir a URI e por separar dos parâmetros GET
     */
    private function setUri()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $xUri = explode('?', $uri);
        $this->uri = $xUri[0];
    }

    /**
     * Método responsável por sanitizar os dados da requisição
     */
    private function sanitize(array $params): array
    {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $this->sanitize($value);
            } else {
                if (is_string($value)) {
                    $value = trim($value);
                }
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /**
     * Método responsável por garantir que os parâmetros tenham conteúdo
     */
    private function validateParamsPost($postValidated)
    {
        foreach ($postValidated as $key) {
            if ($this->postVars[$key]) {
                $array[$key] = $this->postVars[$key];
            }
        }
        $this->postVars = $array;
    }
}
