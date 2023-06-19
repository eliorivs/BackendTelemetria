<?php
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


      mysqli_set_charset($conn, "utf8");

      $sql="SELECT id_date,description FROM def_date ";
      $result = $conn->query($sql);
       while ($row = $result->fetch_array(MYSQLI_ASSOC))
       {

        $response[] = array('id_date'=>$row['id_date'],'descripcion'=>$row['description']);
       }
        echo json_encode($response);



      ?>