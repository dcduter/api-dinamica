    <?php

    require_once "models/connection.php";
    require_once "controllers/delete.controller.php";

    // se pregunta si viena la variable id y nameId
    if(isset($_GET['id']) && isset($_GET['nameId'])){

        $columns = array($_GET["nameId"]);


        /* =================================
          Validar tablas y columnas       
          =============================== */

          // si al comprobar la tabla y las columnas esta vacio con getColumnsData de conexion no coincide los nombres
          if(empty(Connection::getColumnsData($table, $columns))){

            // entrega un formato json
            $json = array (

                'status' => 400,
                'result' => 'Error al recibir las columnas'
                
            );

            // se envia la respuesta al cliente
            echo json_encode($json, http_response_code($json["status"]));

            return;

          }
          
          /* ======================================
            Solicitamos respuesta del controlador para eliminar datos de tablas
            ====================================== */

            $response = new DeleteController();
            $response->deleteData($table, $_GET['id'], $_GET['nameId']);
    }
