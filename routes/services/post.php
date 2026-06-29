<?php
// ====== RECIVE LOS DATOS DE ROUTES.PHP
require_once 'models/connection.php';  // para verificar si existe la tabla y sus columnas
require_once 'controllers/post.controller.php';  // para crear los datos

// validar que la peticion sea POST
if (isset($_POST)) {
    // se difine una variable con una array vacio y luego se pasan lo nombres obtenidos del foreach
    $columns = array();

    // con un foreach se separa los nombre de las propiedades
    foreach (array_keys($_POST) as $key => $value) {
        array_push($columns, $value);
    }

    /* ====================================================
       VALIDAR QUE LA TABLA Y SUS COLUMNAS EXISTAN
       ===============================================*/
    // ! se usa para invertir el resultado si false == true y si es true == false, si es false seria verdadero y se ejecuta el if
    if (!Connection::getColumnsData($table, $columns)) {
        // si algo esta mal se ejecutucuta
        $json = array(
            'status' => 404,
            'result' => 'Escriba de nuevo el nombre de la tabla (POST)'
        );

        // json_encode permite devolve el status obtenido en el array en forma de json
        echo json_encode($json, http_response_code($json['status']));
        // return para detener la ejecucion del codigo
        return;
    }

    $response = new PostController();

    /* ====================================================================
   Peticion POST para el registro de usuarios
    ====================================================================*/

    if (isset($_GET['register']) && $_GET['register'] === 'true') {
        // se pregunta si viene la variable suffix con ?? para indicar un valor predeterminado
        $suffix = $_GET['suffix'] ?? 'user';

        // se pide una respuesta la controlador
        // en le controlador se crea en metodo para registro de usuario
        $response->postRegister($table, $_POST, $suffix);

        /* ====================================================================
         Peticion POST para el login de usuarios
         ====================================================================*/
    } else if (isset($_GET['login']) && $_GET['login'] == 'true') {
        // se pregunta si viene la variable suffix con ?? para indicar un valor predeterminado
        $suffix = $_GET['suffix'] ?? 'user';

        // se pide una respuesta la controlador
        // en le controlador se crea en metodo para registro de usuario
        $response->postLogin($table, $_POST, $suffix);
    } else {
        /* ====================================================================
           Peticion POST para usuarios autenticados
       ====================================================================*/

        if (isset($_GET['token'])) {


          /* =============================================
              Peticion PUT para usuarios no autorizados 
              ===========================================*/
          if($_GET["token"] == "no" && isset($_GET["except"])){



            $columns = array($_GET["except"]);

             /* ====================================================
                VALIDAR QUE LA TABLA Y SUS COLUMNAS EXISTAN
             ===============================================*/
                    // ! se usa para invertir el resultado si false == true y si es true == false, si es false seria verdadero y se ejecuta el if
            if (!Connection::getColumnsData($table, $columns)) {
                    // si algo esta mal se ejecutucuta
                 $json = array(
                   'status' => 404,
                   'result' => 'Escriba de nuevo el nombre de la tabla (POST)'
                    );

                    // json_encode permite devolve el status obtenido en el array en forma de json
                    echo json_encode($json, http_response_code($json['status']));
                    // return para detener la ejecucion del codigo
                    return;
                }

                // se pide respuesta del controlador para crear datos en cualquier tabla
                $response -> postData($table, $_POST);

          }else{
                    
                    /* =============================================
                      Peticion POST para usuarios autorizados 
                      ===========================================*/
                      
                    // si no viene table y suffix su valor sera users
                    $tableToken = $_GET['table'] ?? 'users';
                    $suffix = $_GET['suffix'] ?? 'user';


                    $validate = Connection::tokenValidate($_GET['token'],$tableToken,$suffix);

                    /* ====================================================================
                    Solicitamos respuesta del controlador para crear datos en cualquier tabla
                    ====================================================================*/

                    if($validate === "ok"){
                    
                    // se llama la metodo postData
                    $response->postData($_POST, $table);
                    } 
                    
                    /* =========================
                    Token expirado
                    ========================= */
                    else if($validate === "expired"){
                        $json = array(
                            'status' => 303,
                            'results' => "Error: Token expirado"
                        );
                        echo json_encode($json, http_response_code($json['status']));
                        return;
                    }
                    
                    /* =========================
                    el token no coincide en la BD
                    ========================= */
                    else {
                        $json = array(
                            'status' => 400,
                            'results' => "Error: Usuario no autorizado"
                        );
                        echo json_encode($json, http_response_code($json['status']));
                        return;
                    }
                }
            
            /*======================================
              se solicita token para realizar la accion
              =========================================*/
        }else{

        $json = array(
            'status' => 400,
            'results' => "Error: Autorización requerida"
        );
        echo json_encode($json, http_response_code($json['status']));
        return;
        }
    }

}
