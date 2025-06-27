<?php

namespace NinjaBox\Controllers {

    use function NinjaBox\Errors\ErrorFileNotFound;
    use function NinjaBox\Errors\ErrorFileNotFoundOrInvalidExt;

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "Error" . DIRECTORY_SEPARATOR . "Errors.php";

    /**
     * Clase para manejar la petición HTTP.
     * 
     * Contiene información básica de la solicitud actual, como método, URI y datos recibidos por GET y POST.
     */
    class request
    {
        public string $method;
        public string $uri;
        public array $query = [];

        /**
         * Constructor que inicializa la instancia con los datos de la petición HTTP actual.
         */
        public function __construct()
        {
            $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $this->uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

            $this->query["GET"] = $_GET ?? [];

            if ($this->method !== 'GET') {
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

                if (str_starts_with($contentType, 'application/json')) {
                    $json = file_get_contents("php://input");
                    $this->query["POST"] = json_decode($json, true) ?? [];
                } else {
                    $this->query["POST"] = $_POST ?? [];
                }
            } else {
                $this->query["POST"] = [];
            }
        }
    }

    /**
     * Clase para manejar la respuesta HTTP.
     * 
     * Provee métodos para enviar respuestas al cliente en diferentes formatos
     * (texto plano, JSON, archivos, renderizado de archivos PHP/HTML/TXT).
     */
    class response
    {
        /**
         * Establece el código de estado HTTP de la respuesta.
         *
         * @param int $code Código de estado HTTP (por defecto 200 OK).
         * @return $this La instancia actual para permitir encadenamiento.
         */
        public function status(int $code = 200): self
        {
            http_response_code($code);
            return $this;
        }

        /**
         * Envía texto plano como respuesta.
         *
         * @param string $content Texto a enviar al cliente.
         * @return $this La instancia actual para permitir encadenamiento.
         */
        public function send(string $content): self
        {
            echo $content;
            return $this;
        }

        /**
         * Envía contenido JSON como respuesta, estableciendo el header Content-Type adecuado.
         *
         * @param string|array $content Datos a codificar en JSON (puede ser string o array).
         * @return $this La instancia actual para permitir encadenamiento.
         */
        public function json(string|array $content): self
        {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return $this;
        }

        /**
         * Renderiza un archivo de texto plano, HTML o PHP como respuesta.
         * - Para archivos PHP, ejecuta el código.
         * - Para otros archivos permitidos (txt, html), envía su contenido como texto plano.
         * 
         * @param string $path Ruta al archivo a renderizar.
         * @return $this La instancia actual para permitir encadenamiento.
         * 
         * @throws Lanza errores personalizados si el archivo no existe o la extensión no es permitida.
         */
        public function render(string $path): self
        {
            if (!file_exists($path)) {
                ErrorFileNotFound($path);
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if (!in_array($ext, ["txt", "php", "html"])) {
                ErrorFileNotFoundOrInvalidExt($path, "La extensión '$ext' no está permitida.");
            }

            if ($ext === "php") {
                require $path;
                return $this;
            }

            $contenido = file_get_contents($path);

            switch ($ext) {
                case 'html':
                    header('Content-Type: text/html; charset=utf-8');
                    break;

                default:
                    header('Content-Type: text/plain; charset=utf-8');
                    break;
            }
            echo $contenido;

            return $this;
        }

        /**
         * Envía un archivo para descarga forzada.
         * Configura los headers HTTP adecuados para que el navegador descargue el archivo.
         *
         * @param string $path Ruta al archivo a descargar.
         * @return $this La instancia actual para permitir encadenamiento.
         * 
         * @throws Lanza error personalizado si el archivo no existe.
         */
        public function file(string $path): self
        {
            if (!file_exists($path)) {
                ErrorFileNotFound($path);
            }

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($path) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($path));

            flush();
            readfile($path);

            return $this;
        }

        /**
         * Destructor que finaliza la ejecución del script después de enviar la respuesta.
         */
        function __destruct()
        {
            exit;
        }
    }
}
