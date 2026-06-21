<?php
// RECIVO LOS DEL CONTROLLADOR

require_once "connection.php";

require_once "get.model.php";

class DeleteModel
{



    /* ===================================================
    Metodo para eliminar datos de forma dinamica
   ===============================================*/

    static public function deleteData($table, $id, $nameId)
    {

        /* =================
          Validar el id      
        ================== */
        $response = GetModel::getDataFilter($table, $nameId, $nameId, $id, null, null, null, null);

        if (empty($response)) {

            return null;

        }


        /* ========================================
          Proceso para eliminar datos de forma dinamica
        ============================================ */
        // se crea dos variable vacias para columnas y parametros
        // DELETE FROM courses WHERE id_course = 1

        $sql = "DELETE FROM $table WHERE $nameId = :$nameId";
        $link = Connection::connectDataBase();
        $stmt = $link->prepare($sql);
        // enlace de parametros

        // enlazamos el parametro $nameId para la condicion WHERE
        $stmt->bindParam(":" . $nameId, $id, PDO::PARAM_STR);



        // se ejecuta
        if ($stmt->execute()) {

            $response = array(

                //     "lastId" => $link -> lastInsertId(), // para ver el ultimo id insertado, por eso la conexion se coloco en $link
                "comment" => "borrado correctamente"

            );
            return $response;

        } else {

            return $link->errorInfo();

        }

    }
}

