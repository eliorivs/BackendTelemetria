<?php

header('Content-type: application/json');
include_once ('conexion.php');   
header("Access-Control-Allow-Origin: *");
header('Content-type: application/json');
require "conexion.php";
  $resultado = array();

if (isset($_SERVER['HTTP_ORIGIN'])) {

        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day

    }

    // Access-Control headers are received during OPTIONS requests

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");      

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers:{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        exit(0);

    }


$sectores = getSectores();


print json_encode($sectores);

     function getSectores() {

      	global $conn;      	
      	$ne = 0; 
      
         $sql = "SELECT  
                            s.*
                            ,AVG(es.latitud) AS latitud
                            ,AVG(es.longitud) AS longitud
                    FROM 
                            sectores s
                            INNER JOIN sectores_estaciones se ON s.id_sector = se.id_sector
                            INNER JOIN estaciones es ON se.id_estacion = es.id_estacion
						    INNER JOIN estaciones_usuarios eu ON (eu.id_estacion = es.id_estacion )
                    GROUP BY s.id_sector;";


            $result = $conn->query($sql);     
            while ($row = $result->fetch_array(MYSQLI_ASSOC)){

            	

            	$datos[] = array(
            		'nombre'=> $row['nombre'],'id_sector'=> $row['id_sector'],'latitud'=> $row['latitud'],'longitud'=>$row['longitud'],'estaciones'=>  getEstaciones($row['id_sector']));
               
            }

            return ($datos);
      
    }
    function getEstaciones($id_sector){

    	 global $conn;
    	 $estaciones;
    	 $sql="SELECT es.nombre from estaciones es,sectores_estaciones se WHERE es.id_estacion=se.id_estacion AND se.id_sector='".$id_sector."'";
    	 $result = $conn->query($sql);
    	 while ($row = $result->fetch_array(MYSQLI_ASSOC)){ 

                $estaciones []= "-".$row['nombre'];

    	 }
        return $estaciones;

    }

 
    



?>