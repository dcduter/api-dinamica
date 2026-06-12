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

// [Cambio por IA] Se agregaron comprobaciones con isset() para evitar advertencias de tipo "Undefined array key" cuando no se envían los parámetros orderBy y orderMode.
//capturamos la el orderBy, se pregunta si existe en la URL; si no, se pone null
$orderBy = (isset($_GET["orderBy"])) ? $_GET["orderBy"] : null;
// usando ?? para orderBy y orderMode: 
//$orderBy = $_GET["orderBy"] ?? null;
//$orderMode = $_GET["orderMode"] ?? null;

//capturamos la el orderMode, se pregunta si existe en la URL; si no, se pone null
$orderMode = (isset($_GET["orderMode"])) ? $_GET["orderMode"] : null;    

// Solicita la información al Controlador
$response = new GetController(); // se instacia la clase para obtener la funcion getData

// [Cambio por IA] Se agregaron comprobaciones con isset() para los parámetros de paginación startAt y endAt.
/* ---- variables para limitar la cantidad de registros a mostrar*/
$startAt = (isset($_GET["startAt"])) ? $_GET["startAt"] : null;  
$endAt = (isset($_GET["endAt"])) ? $_GET["endAt"] : null;  
/*  ----------------------------------------- */


/* ========= peticiones con filtro ========= */
// se pregunta si viene el GET con linkTo y equalTo
if(isset($_GET['linkTo']) && isset($_GET['equalTo'])){
    
    $response -> getDataFilter($table, $select, $_GET["linkTo"], $_GET["equalTo"], $orderBy, $orderMode, $startAt, $endAt); // se ejecuta la funcion getData
    

    //------------------------------------------------------------------------------------------------- 
/*  === peticiones get si filtro entre tablas relacionadas === */
// si trae el parmetros de rel y type y la table es relations se ejecuta sis filtros
}else if(isset($_GET['rel']) && isset($_GET['type']) && $table == "relations" && !isset($_GET['linkTo']) && !isset($_GET['equalTo'])){

     $response -> getRelData($_GET['rel'], $_GET['type'], $select, $orderBy, $orderMode, $startAt, $endAt);

}else{
    /* ========= peticiones sin filtro ========= */
    //peticion sin filtro
    $response -> getData($table, $select, $orderBy, $orderMode, $startAt, $endAt); // se ejecuta la funcion getData soliciara lo parametros desde controllers/get.controller.php       
}




