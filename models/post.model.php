<?php
// RECIVO LOS DEL CONTROLLADOR

require_once "connection.php";



class PostModel{

    /* ===================================================
    Metodo para crear datos de forma dinamica
   ===============================================*/

    static public function postData($table, $data){


        // se crea dos variable vacias para columnas y parametros
        $columns = "";
        $params = "";

        
       foreach ($data as $key => $value) {

        //nombre de las columnas en formato string separados por coma
        $columns .= $key . ",";
        //parametros en formato string separados por coma
        $params .= ":" . $key . ",";
       }
       // eliminar la ultima coma de cada variable
       $columns = substr($columns,0,-1);
       $params = substr($params,0,-1);


       // $sql = "INSERT INTO `courses`(`id_course`, `title_course`, `description_course`, `id_instructor_course`, `image_course`, `price_course`, `date_created_course`, `date_updated_course`) VALUES ('[value-1]','[value-2]','[value-3]','[value-4]','[value-5]','[value-6]','[value-7]','[value-8]')";

        // asi sirve para cualquier tabla pero no para columna, falta hacerla dinamica
        $sql = "INSERT INTO $table ($columns) VALUES ($params)";

        // enlazar los parametros
        $link = Connection::connectDataBase();
        $stmt = $link -> prepare($sql);

        foreach ($data as $key => $value) {
            // se hace bind para enlazar los parametros y se  
            $stmt -> bindParam(":".$key, $data[$key], PDO::PARAM_STR); // se le agrega pdo para que sea mas seguro
        }

        // se ejecuta
        if($stmt -> execute()){

            $response = array(

                "lastId" => $link -> lastInsertId(), // para ver el ultimo id insertado, por eso la conexion se coloco en $link
                "comment" => "todo bien"
                
            );
            return $response;

        }else{

            return $link->errorInfo();
            
          }
    
    }
}
    
    