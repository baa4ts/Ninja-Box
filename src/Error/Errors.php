<?php

namespace NinjaBox\Errors {

    function ErrorNewPath(string $path)
    {
        header("HTTP/1.0 404 Not Found");
        echo "<h1>Error 404 - Ruta inválida</h1>";
        echo "<p>Se ingresó una ruta no válida: <strong>" . htmlspecialchars($path) . "</strong></p>";
        echo "<p>Posibles errores:</p>";
        echo "<ul>";
        echo "<li>El método HTTP no fue especificado.</li>";
        echo "<li>La ruta está vacía o es demasiado corta.</li>";
        echo "<li>No se proporcionó un callback válido.</li>";
        echo "</ul>";
        exit;
    }

    function ErrorDuplicatedPath(string $path)
    {
        header("HTTP/1.0 409 Conflict");
        echo "<h1>Error 409 - Ruta duplicada</h1>";
        echo "<p>La ruta <strong>" . htmlspecialchars($path) . "</strong> ya está registrada.</p>";
        echo "<p>No se pueden registrar rutas duplicadas con el mismo método HTTP.</p>";
        echo "<p>Posibles soluciones:</p>";
        echo "<ul>";
        echo "<li>Revisa si ya registraste esa ruta con el mismo método (`GET`, `POST`, etc.).</li>";
        echo "<li>Usa una ruta diferente o modifica la existente.</li>";
        echo "<li>Asegúrate de no incluir accidentalmente el mismo archivo varias veces.</li>";
        echo "</ul>";
        exit;
    }

    function ErrorFileNotFound(string $path)
    {
        header("HTTP/1.0 404 Not Found");
        echo "<h1>Error 404 - Archivo no encontrado</h1>";
        echo "<p>El archivo que intentas descargar no existe: <strong>" . htmlspecialchars($path) . "</strong></p>";
        echo "<p>Posibles causas:</p>";
        echo "<ul>";
        echo "<li>La ruta del archivo es incorrecta o está mal escrita.</li>";
        echo "<li>El archivo fue movido o eliminado del servidor.</li>";
        echo "<li>No tienes permisos para acceder al archivo.</li>";
        echo "</ul>";
        exit;
    }


    function ErrorFileNotFoundOrInvalidExt(string $path, ?string $reason = null)
    {
        header("HTTP/1.0 404 Not Found");
        echo "<h1>Error 404 - Archivo no encontrado o extensión no permitida</h1>";
        echo "<p>El archivo que intentas descargar no existe o no puede ser renderizado: <strong>" . htmlspecialchars($path) . "</strong></p>";
        echo "<p>Posibles causas:</p>";
        echo "<ul>";
        echo "<li>La ruta del archivo es incorrecta o está mal escrita.</li>";
        echo "<li>El archivo fue movido o eliminado del servidor.</li>";
        echo "<li>No tienes permisos para acceder al archivo.</li>";
        echo "<li>La extensión del archivo no está permitida para ser descargada o mostrada.</li>";
        echo "</ul>";

        if ($reason) {
            echo "<p><strong>Detalles:</strong> " . htmlspecialchars($reason) . "</p>";
        }

        exit;
    }

    function ErrorInvalidMiddleware(string $middleware)
    {
        header("HTTP/1.0 500 Internal Server Error");
        echo "<h1>Error 500 - Middleware inválido</h1>";
        echo "<p>El middleware especificado no es válido: <strong>" . htmlspecialchars($middleware) . "</strong></p>";
        echo "<p>Posibles causas:</p>";
        echo "<ul>";
        echo "<li>El middleware no es una función o clase invocable.</li>";
        echo "<li>El middleware no existe o no fue incluido correctamente.</li>";
        echo "<li>El middleware no implementa la interfaz o la firma esperada.</li>";
        echo "</ul>";
        echo "<p>Verifica que esté correctamente definido y registrado.</p>";
        exit;
    }

    function ErrorInvalidCallback(mixed $callback)
    {
        header("HTTP/1.0 500 Internal Server Error");

        echo "<h1>Error 500 - Callback inválido</h1>";
        echo "<p>El callback proporcionado no es invocable.</p>";
        echo "<p>Solo se aceptan funciones, closures o estructuras <code>callable</code> válidas como callbacks.</p>";
        echo "<p>Verifica que estés pasando una función existente o un método accesible.</p>";
        echo "<p>Tu callback: <pre>" . htmlspecialchars(print_r($callback, true)) . "</pre></p>";
        exit;
    }
}
