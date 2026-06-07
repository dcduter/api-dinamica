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

$table = explode("?", $routesArray[1])[0]; // con explode se genera un array separado por ? y se captura el primer indice que es la tabla a consultar


// preguntamos si viene un variable select de la url con el operaddro ternario
$select = (isset($_GET['select'])) ? $_GET['select'] : "*"; // Columnas a seleccionar (todas por defecto)

// Solicita la información al Controlador
$response = new GetController(); // se instacia la clase para obtener la funcion getData


/* ========= peticiones con filtro ========= */
// se pregunta si viene el GET con linkTo y equalTo
if(isset($_GET['linkTo']) && isset($_GET['equalTo'])){
    
    $response -> getDataFilter($table, $select, $_GET["linkTo"], $_GET["equalTo"]); // se ejecuta la funcion getData      

}else{
    /* ========= peticiones sin filtro ========= */
    //peticion sin filtro
    $response -> getData($table, $select); // se ejecuta la funcion getData      
}




