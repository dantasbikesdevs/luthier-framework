<?php declare(strict_types=1);

namespace Luthier\Http;

use Luthier\Utils\ClassName;
use Luthier\Http\Middlewares\Queue as Middleware;

class Middlewares
{
    private static ?string $path = "";
    private static array $middlewares = [];

    /**
     * Método responsável por configurar o caminho dos middlewares da aplicação e do framework e mapeá-los automaticamnete.
     *
     * @param string $path
     */
    public static function config(string $path = ""): void
    {
        self::$path = $path;
        self::mapMiddlewares();
        self::setMiddlewares();
    }

    /**
     * Método responsável por mapear os middlewares da aplicação e do framework.
     */
    private static function mapMiddlewares(): void
    {
        self::mapMiddlewaresFramework();
        if (!empty(self::$path)) self::mapMiddlewaresApplication();
    }

    /**
     * Método responsável por mapear os middlewares do framework.
     */
    private static function mapMiddlewaresFramework(): void
    {
        $path = realpath(dirname(__DIR__)) . "/Http/Middlewares";
        $dir = dir($path);

        while ($file = $dir->read()) {
            if ($file == "." || $file == "..") continue;
            $dirname = $path . DIRECTORY_SEPARATOR . $file;
            $class = ClassName::getClassNameFromFile($dirname);
            if (empty($class)) continue;
            $middlewareKey = "luthier:" . lcfirst($class);
            $namespacePath = "Luthier\\Http\\Middlewares\\" . $class;
            self::$middlewares[$middlewareKey] = $namespacePath;
        }
    }

    /**
     * Método responsável por mapear os middlewares da aplicação.
     */
    private static function mapMiddlewaresApplication(): void
    {
        $path = realpath(dirname(__DIR__, 5)) . "/" . self::$path;

        $dir = dir($path);

        while ($file = $dir->read()) {
            if ($file == "." || $file == "..") continue;
            $dirname = $path . DIRECTORY_SEPARATOR . $file;
            $class = ClassName::getClassNameFromFile($dirname);
            $namespace = ClassName::getClassnamespaceFromFile($dirname);
            if (empty($class) || empty($namespace)) continue;
            $middlewareKey = lcfirst($class);
            $namespacePath = $namespace . "\\" . $class;
            self::$middlewares[$middlewareKey] = $namespacePath;
        }
    }

    /**
     * Método responsável por setar os middlewares na fila de execução.
     */
    private static function setMiddlewares(): void
    {
        Middleware::map(self::$middlewares);
    }

    /**
     * Método responsável por setar os middlewares padrões.
     */
    public static function setDefault($defaults): void
    {
        Middleware::addDefaults($defaults);
    }
}
