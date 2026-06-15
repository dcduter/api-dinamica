<?php

/**
 * Punto de Entrada Principal (Bootstrap)
 * 
 * [Cambio por IA]
 * Este es el archivo raíz donde inician absolutamente todas las peticiones HTTP a la API.
 * Configura la directiva de errores de PHP, requiere el controlador de rutas base,
 * e instancia e inicia el flujo del sistema.
 */

/* -------------------------- mostra errores en pantalla -------------------------- */
ini_set('display_errors', 1);  // muestra los errores
ini_set('log_errors', 1); // guardar los errores en un archivo
ini_set('error_log', "C:/wamp64/www/apirest-dinamica/errores_log"); // generar un archivo con los errores

/* ------------------------------------------------------------------------ */
/* -----------------------------------llama las rutas ------------------------------------ */
require_once "controllers/routes.contoller.php";

//  se instacio la una clase nueva
$index = new routesController();

// se llama al metodo index con la variable $index
$index->index();
?>