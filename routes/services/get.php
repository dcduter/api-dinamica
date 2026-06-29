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

 // con explode se genera un array separado por ? y se captura el primer indice que es la tabla a consultar

// preguntamos si viene un variable select de la url con el operaddro ternario 
$select = (isset($_GET['select'])) ? $_GET['select'] : "*"; // Columnas a seleccionar (todas por defecto)

// [Cambio por IA] Se agregaron comprobaciones con isset() para evitar advertencias de tipo "Undefined array key" cuando no se envían los parámetros orderBy y orderMode.
$orderBy = (isset($_GET["orderBy"])) ? $_GET["orderBy"] : null;
// usando ?? para orderBy y orderMode: 
//$orderBy = $_GET["orderBy"] ?? null;
//$orderMode = $_GET["orderMode"] ?? null;

$orderMode = (isset($_GET["orderMode"])) ? $_GET["orderMode"] : null;
$startAt = (isset($_GET["startAt"])) ? $_GET["startAt"] : null;
$endAt = (isset($_GET["endAt"])) ? $_GET["endAt"] : null;
$filterTo = (isset($_GET["filterTo"])) ? $_GET["filterTo"] : null;
$inTo = (isset($_GET["inTo"])) ? $_GET["inTo"] : null;
$response = new GetController(); // se instacia la clase para obtener la funcion getData
/*  ----------------------------------------- */


/* ================== peticiones con filtro ================== */

// [Cambio por IA] Se agregó '&& $table != "relations"' para evitar que las consultas de tablas relacionadas con filtros entren en esta sección de consulta de tabla física única. se le puede poner un !isset($_GET['rel']) y un !isset($_GET['type']) para que pueda entrar en relaciones con filtro o $table != "relations"
if (isset($_GET['linkTo']) && isset($_GET['equalTo']) && $table != "relations" && !isset($_GET['rel']) && !isset($_GET['type']))  {

    $response->getDataFilter($table, $select, $_GET["linkTo"], $_GET["equalTo"], $orderBy, $orderMode, $startAt, $endAt); // se ejecuta la funcion getData
    //------------------------------------------------------------------------------------------------- 
   
}
/*  === peticiones get sin filtro entre tablas relacionadas === */

 // si trae el parmetros de rel y type y la table es relations se ejecuta sis filtros
else if (isset($_GET['rel']) && isset($_GET['type']) && $table == "relations" && !isset($_GET['linkTo']) && !isset($_GET['equalTo'])) {

    $response->getRelData($_GET['rel'], $_GET['type'], $select, $orderBy, $orderMode, $startAt, $endAt);

}
/*  === peticiones get con filtro entre tablas relacionadas === */
// si trae el parmetros de rel y type y la table es relations se ejecuta sis filtros
else if (isset($_GET['rel']) && isset($_GET['type']) && $table == "relations" && isset($_GET['linkTo']) && isset($_GET['equalTo'])) {

    $response->getRelDataFilter($_GET['rel'], $_GET['type'], $select, $_GET['linkTo'], $_GET['equalTo'], $orderBy, $orderMode, $startAt, $endAt);

}/* ============= Peticiones GET para el buscador sin relaciones ============= */ 
else if (isset($_GET['linkTo']) && isset($_GET['search']) && !isset($_GET['rel']) && !isset($_GET['type'])) {

    $response->getDataSearch($table, $select, $_GET['linkTo'], $_GET['search'], $orderBy, $orderMode, $startAt, $endAt);

}/* ============= Peticiones GET para el buscador con relaciones ============= */ 
else if (isset($_GET['rel']) && isset($_GET['type']) && $table == "relations" && isset($_GET['linkTo']) && isset($_GET['search'])) {

    $response->getRelDataSearch($_GET['rel'], $_GET['type'], $select, $_GET['linkTo'], $_GET['search'], $orderBy, $orderMode, $startAt, $endAt);

}
/* ============ Peticines GET para selecion de rangos ========== */ 
else if (isset($_GET['linkTo']) && isset($_GET['between1']) && isset($_GET['between2']) && !isset($_GET['rel']) && !isset($_GET['type'])) {

    $response->getDataRange($table, $select, $_GET['linkTo'], $_GET['between1'], $_GET['between2'], $orderBy, $orderMode, $startAt, $endAt, $filterTo, $inTo);


}
/* ============ Peticines GET para selecion de rangos con relacioines ========== */ 
else if (isset($_GET['linkTo']) && isset($_GET['between1']) && isset($_GET['between2']) && isset($_GET['rel']) && isset($_GET['type']) && $table == "relations") {

    $response->getRelDataRange($_GET['rel'], $_GET['type'], $select, $_GET['linkTo'], $_GET['between1'], $_GET['between2'], $orderBy, $orderMode, $startAt, $endAt, $filterTo, $inTo);

}
else {
    /* ========= peticiones sin filtro ========= */
    //peticion sin filtro
    $response->getData($table, $select, $orderBy, $orderMode, $startAt, $endAt); // se ejecuta la funcion getData soliciara lo parametros desde controllers/get.controller.php       
}




