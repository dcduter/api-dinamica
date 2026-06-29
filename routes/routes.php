<?php

/**
 * Enrutador Principal de la API
 * 
 * [Cambio por IA]
 * Este archivo se encarga de analizar la petición HTTP, segmentar e identificar
 * la URL solicitada, comprobar el método HTTP y canalizarla al servicio correspondiente
 * (como routes/services/get.php para peticiones de tipo GET).
 */
require_once "models/connection.php";
require_once "controllers/get.controller.php";

$routesArray = explode("/", $_SERVER['REQUEST_URI']); // captuar la url
$routesArray = array_filter($routesArray); // esto hace que se quite la primera posicion del array que es null
/* CUANDO NO SE HACEN PETICIONES A LA API SE MUESTRA UN REGISTRO NO ENCONTRADO */
//las respuesta deben ser en formato json
if (count($routesArray) == 0) {
    $json = array(
        'status' => 404,
        'result' => 'Registro no encontrado'
    );


    // se hace un echo con la funcion json_encode para que se muestre en formato json
    echo json_encode($json, http_response_code($json["status"]));
    return; /* para que no se ejecute el codigo que sigue */
}
/* CUANDO SI SE HACEN PETICIONES SE EJECUTA EL CODIGO QUE SIGUE */
if (count($routesArray) == 1 && isset($_SERVER['REQUEST_METHOD'])) {


    // se coloca aca para que pueda ser usado en post.php
    // se elimina el ? y se captura el primer indice que es la tabla a consultar
    $table = explode("?", $routesArray[1])[0];
    
    /* ================================
       Validar clave secreta
    ================================= */

    // getallheaders se usa para obtener todos los headers de la peticion
    if (!isset(getallheaders()["Authorization"]) || getallheaders()["Authorization"] != Connection::apiKey()) {

        if (in_array($table, Connection::publicAccess()) == 0) {

            $json = array(
                'status' => 400,
                'results' => "No esta autorizado para esa peticion loka"
            );

            echo json_encode($json, http_response_code($json["status"]));
            return;
        }else{
            /* ============================
              Acceso publico
              =============================== */

              $response = new GetController();
              $response -> getData($table, "*", null, null, null, null);

              return; 

        }

    }



    //================= peticiones GET =================
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {


        include "routes/services/get.php";

    }

    //================= peticiones POST =================
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        // peticiones POST 
        include "routes/services/post.php";

    }

    //================= peticiones PUT =================
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {

        // peticiones PUT 
        include "routes/services/put.php";


    }

    if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {

        include 'routes/services/delete.php';
    }
}