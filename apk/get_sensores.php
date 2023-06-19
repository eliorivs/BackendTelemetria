<?php
	header('Content-type: application/json');
	require "conexion.php";
        require "conversions.php";
        require "credencials.php";




 $postdata = file_get_contents("php://input");
    if (isset($postdata)) {
        $request = json_decode($postdata);       
        $id_estacion = $request->estacion;       
     
        if (($id_estacion!=""))
	    {

		   $data =  DevicesList($id_estacion);
		   print json_encode($data,JSON_PRETTY_PRINT);

        
          
        }
        else
        {
            echo "data is empty!";
        }
    }
    else {
        echo "Not called properly with username parameter!";
    }


	/*$postdata = file_get_contents("php://input");
	 if (isset($postdata)) {
		$request = json_decode($postdata);    
		$id_estacion = $request->estacion;		

		   if($id_estacion != "")
		   {
		  
		   $data =  DevicesList($id_estacion);
		   print json_encode($data,JSON_PRETTY_PRINT);
	     
		
		  
		  }
		else {
		    echo "Empty std parameter!";
		}
	    }
	    else {
		echo "Not called properly with username parameter!";
	    }

        //$id_estacion ='15';	//esto se envia desde un post
	//$data =  DevicesList($id_estacion);
        //$output =array('parametros'=>$data,'estacion'=>'EW4-1');
	//print json_encode($data,JSON_PRETTY_PRINT);
*/
	function DevicesList($id_estacion)
	{
	    global $conn;
	    $data=array();
 	    $sql = "SELECT a.entrada,a.desc_larga FROM sensores a , equipos b, estaciones_equipos c , estaciones d WHERE a.id_equipo=b.id_equipo AND c.id_equipo=b.id_equipo AND d.id_estacion=c.id_estacion AND c.id_estacion='".$id_estacion."'";
	    $result = $conn->query($sql);
	    while ($row = $result->fetch_array(MYSQLI_ASSOC))
            {
		 $data[]=array('id'=>$row['entrada'],'name'=>$row['desc_larga']);
                 
   	    }
	    return $data;
        }


?>
