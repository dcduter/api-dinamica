<?php

/**
 * Clase routesController
 * 
 * [Cambio por IA]
 * Controlador principal de arranque. Su única función es delegar el procesamiento
 * de la solicitud al enrutador principal en routes/routes.php.
 * 
 * @category Controller
 * @package  Controllers
 */
class routesController{
    
    /**
     * Metodo index para conectar con la ruta principal.
     * 
     * [Cambio por IA]
     * Invocado desde index.php para dar inicio al flujo de enrutamiento del sistema
     * incluyendo el archivo routes/routes.php.
     * 
     * @return void
     */
    public static function index(){
        include "routes/routes.php";
    }

} 
?>