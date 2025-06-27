<?php

namespace baa4ts {

    require_once __DIR__ . DIRECTORY_SEPARATOR . "Error" . DIRECTORY_SEPARATOR . "Errors.php";
    require_once __DIR__ . DIRECTORY_SEPARATOR . "Controllers" . DIRECTORY_SEPARATOR . "Controllers.php";

    use function NinjaBox\Errors\ErrorDuplicatedPath;
    use function NinjaBox\Errors\ErrorInvalidCallback;
    use function NinjaBox\Errors\ErrorInvalidMiddleware;
    use function NinjaBox\Errors\ErrorNewPath;

    use NinjaBox\Controllers\request;
    use NinjaBox\Controllers\response;

    /**
     * NinjaBox Framework
     * @version 1.0.0
     */
    final class NinjaBox
    {
        protected array $PATH = [];

        /**
         * Registra una nueva ruta con método, middleware y callback.
         *
         * @param string $method Método HTTP (GET, POST, etc.)
         * @param string $path Ruta para la petición
         * @param callable|array $middleware Middleware(s) a ejecutar antes del callback
         * @param callable $callback Función principal a ejecutar para la ruta
         * @return void
         */
        public function New(
            string $method = "GET",
            string $path = "/",
            callable|array $middleware,
            callable $callback
        ): void {
            if (isset($this->PATH[$method][$path])) {
                ErrorDuplicatedPath($path);
            }

            if (trim($path) === "" || empty($callback)) {
                ErrorNewPath($path);
            }

            $this->PATH[$method][$path] = [
                "mid" => $middleware,
                "cal" => $callback,
            ];
        }

        /**
         * Procesa la petición actual y ejecuta middleware y callback correspondientes.
         *
         * @return void
         */
        public function Run(): void
        {
            $req = new request();
            $res = new response();

            $method = $req->method;
            $uri    = $req->uri;

            if (!isset($this->PATH[$method][$uri])) {
                $res->status(404)->json([
                    "error" => "Error 404 - Ruta no encontrada"
                ]);
                return;
            }

            $controller = $this->PATH[$method][$uri];
            $callStack  = [];

            if (!empty($controller["mid"])) {
                $middlewares = is_array($controller["mid"]) ? $controller["mid"] : [$controller["mid"]];

                foreach ($middlewares as $middleware) {
                    if (!is_callable($middleware)) {
                        ErrorInvalidMiddleware(is_string($middleware) ? $middleware : gettype($middleware));
                    }

                    $callStack[] = $middleware;
                }
            }

            if (!is_callable($controller["cal"])) {
                ErrorInvalidCallback($controller["cal"]);
            }

            $callStack[] = $controller["cal"];

            $this->dispatcher($callStack, $req, $res, 0);
        }

        /**
         * Ejecuta recursivamente los middlewares y callback de la pila.
         *
         * @param array $callStack Pila de funciones a ejecutar
         * @param request $req Objeto request con datos de la petición
         * @param response $res Objeto response para enviar respuesta
         * @param int $counter Índice actual en la pila
         * @return void
         */
        private function dispatcher(
            array $callStack,
            request $req,
            response $res,
            int $counter = 0
        ): void {
            if (!isset($callStack[$counter])) {
                return;
            }

            $call = $callStack[$counter];

            $next = function () use ($callStack, $req, $res, $counter) {
                $this->dispatcher($callStack, $req, $res, $counter + 1);
            };

            $call($req, $res, $next);
        }
    }
}
