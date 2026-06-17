<?php
// ====== RECIVE LOS DATOS DE ROUTES.PHP
require_once "models/connection.php"; // para verificar si existe la tabla y sus columnas
require_once "controllers/post.controller.php"; // para crear los datos

// validar que la peticion sea POST 
if(isset($_POST)){

    //se difine una variable con una array vacio y luego se pasan lo nombres obtenidos del foreach
    $columns = array();

    // con un foreach se separa los nombre de las propiedades
    foreach(array_keys($_POST) as $key => $value){
        array_push($columns, $value);
    }

    /* ====================================================
       VALIDAR QUE LA TABLA Y SUS COLUMNAS EXISTAN 
       ===============================================*/
        // ! se usa para invertir el resultado si false == true y si es true == false, si es false seria verdadero y se ejecuta el if
    if(!Connection::getColumnsData($table, $columns)){
        
        // si algo esta mal se ejecutucuta
        $json = array (
            'status' => 404,
            'result' => 'Escriba de nuevo el nombre de la tala'
        );
        
        // json_encode permite devolve el status obtenido en el array en forma de json
        echo json_encode($json, http_response_code($json["status"]));
        // return para detener la ejecucion del codigo
        return;
    }
    
    
    /* ====================================================================
      Solicitamos respuesta del controlador para crear datos en cualquier tabla
       ====================================================================*/
       // instancia la clase post controller
        $response = new PostController();

        // se llama la metodo postData
        $response -> postData($_POST, $table);
}