<?php

/**
 * Servicio GET
 * 
 * [Cambio por IA]
 * Este archivo procesa e integra las peticiones HTTP de tipo GET.
 * Importa el controlador y el modelo requeridos, solicita los datos dinámicos 
 * de la tabla e imprime una respuesta JSON formateada con código de estado HTTP adecuado.
 */

require_once "controllers/get.controller.php";
//require_once "models/get.model.php";

$table = $routesArray[1]; // Nombre de la tabla extraída de la URL

// Solicita la información al Controlador
$response = new GetController(); // se instacia la clase para obtener la funcion getData
$response -> getData($table); // se ejecuta la funcion getData

// Estructura de respuesta HTTP JSON
// if(!empty($response)){

// }
   
// } else {
//     $json = array (
//         'status' => 404,
//         'result' => 'No se encontraron registros en la tabla: ' . htmlspecialchars($table, ENT_QUOTES, 'UTF-8')
//     );
// }
