    <?php

    require_once "models/connection.php";
    require_once "controllers/put.controller.php";

    // se pregunta si viena la variable id y nameId
    if(isset($_GET['id']) && isset($_GET['nameId'])){

    /* ====================================
    Capturar los datos del formulario
    ======================================== */    
        
        $data = array();
        
        // se usa parse_str para que lo pase a un array que se guarda en $data y se elimina los datos vacios con array_filter
        // con file_get_contents se recibe el archivo que viene desde la peticion put 
        parse_str(file_get_contents('php://input'), $data);
        //$data = array_filter($data);

        /* =================================
          Separar la propiedades en un arreglo y el id
        ==================================== */

        $columns = array();

        foreach (array_keys($data) as $key) {
            array_push($columns, $key);
        }

        // validar nameId
        array_push($columns, $_GET['nameId']);
        $columns = array_unique($columns);


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
            Solicitamos respuesta del controlador 
            ====================================== */

            $response = new PutController();
            $response->putData($data, $table, $_GET['id'], $_GET['nameId']);
    }
