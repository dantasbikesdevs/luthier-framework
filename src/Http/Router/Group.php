<?php

declare(strict_types=1);

namespace Luthier\Http\Router;

use DomainException;
use Luthier\Http\Request;
use Luthier\Http\Router\Abstracts\Route as AbstractRoute;
use Luthier\Http\Router\Contracts\Group as GroupInterface;
use Luthier\Http\Router\Contracts\Route as RouteInterface;

class Group extends AbstractRoute implements GroupInterface
{
    public function __construct(
        string  $prefix,
        Request $request
    )
    {
        $this->prefix($prefix);
        parent::__construct("", "", $request);
    }

    /**
     * Método responsável por modificar as rotas do grupo
     * com base no grupo "pai". Neste método, o prefixo, permissões
     * e os middlewares da rota base são setados nas rotas do grupo.
     *
     * @param array<int, RouteInterface> $routes
     */
    public function group(array $routes): void
    {
        foreach ($routes as $route) {
            $route->prefix($this->prefix);
            $route->middlewares($this->middlewares);
            $route->is($this->permissions);
            $route->can($this->rules);
            $route->see($this->screens);

            if (! empty($this->controller->getClassName())) {
                $route->controller($this->controller->getClassName());
            }
        }
    }

    /**
     * Método responsável por setar o prefixo da rota.
     *
     * Neste caso, caso o usuário tente adicionar um prefixo
     * ao grupo, o prefixo que já foi definido anteriormente
     * no método 'prefix' da classe 'Router' será sobrescrito.
     */
    public function prefix(string $prefix): static
    {
        parent::prefix($prefix);

        return $this;
    }
}
