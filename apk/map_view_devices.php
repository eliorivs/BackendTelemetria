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
             
 	       $map = StationMap($id_estacion);
           $sensores =  SensoresStation($id_estacion);
           $reply = array( 'map'=>$map,                     
                           'lecturas'=> GetLecturas($sensores,$map['id_estacion']),
                           'color'=>DeviceList($id_estacion) ); 

           print json_encode($reply,JSON_PRETTY_PRINT);     
        
          
        }
        else
        {
            echo "data is empty!";
        }
    }
    else {
        echo "Not called properly with username parameter!";
    }

    /*$id_estacion = 15;
    $map = StationMap($id_estacion);
    $sensores =  SensoresStation($id_estacion);
    $reply = array( 'map'=>$map,                     
                    'lecturas'=> GetLecturas($sensores,$map['id_estacion']),
                    'color'=>DeviceList($id_estacion) ); 
    print json_encode($reply,JSON_PRETTY_PRINT); */


    function GetLecturas($sensores, $estacion)
    {
          
        $lecturas = array();
        foreach($sensores as $sensor)
            {
              
                $lecturas [] = array('entrada'=>$sensor['entrada'], 
                                     'estacion'=>$estacion,
                                     'desc_larga'=>$sensor['desc_larga'],
                                     'valor'=> round(IEEE754To32Float2(SelectSensor($estacion,$sensor['entrada'])),2),
                                     'unidad'=>GetSymbol($sensor['unidad']));
                
            }
            return $lecturas ;     
    }
    
    function StationMap($id_estacion)
    {
        global $conn;
        $data = array();
        $sql ="select * from estaciones a, ultimas_mediciones b WHERE a.id_estacion=b.id_estacion AND b.id_estacion='".$id_estacion."'";
        $result = $conn->query($sql);
        while ($row = $result->fetch_array(MYSQLI_ASSOC))
        {
            $data = array('estacion'=>$row['nombre'],
                            'area'=>$row['estado'], 
                            'latitud'=>$row['latitud'],
                            'longitud'=>$row['longitud'],
                            'utm_este'=>$row['utm_este'],
                            'utm_norte'=>$row['utm_norte'],
                            'timestamp'=>$row['Timestamp'],
                            'arrive'=>$row['Timestamp_arrive'],
                            'data'=>$row['DATA'],
                            'id_estacion'=>$row['id_estacion'],
                            'actualtime'=>actual_datetime(),
                            );
        }       
        return $data;

    }
    function DeviceList($id_estacion)
    {
        global $conn;       
        $sql = "SELECT f.id_equipo as equipo,a.id_estacion as estacion,a.nombre,b.Timestamp_arrive as Timestamp, c.nombre AS sector, f.config, a.preparedST 
        FROM estaciones a, ultimas_mediciones b, sectores c , sectores_estaciones d  , estaciones_equipos e, equipos_configs f 
        WHERE a.id_estacion=b.id_estacion  AND a.id_estacion=d.id_estacion AND c.id_sector=d.id_sector AND a.id_estacion=e.id_estacion AND e.id_equipo=f.id_equipo and a.id_estacion='".$id_estacion."'";       
        $result = $conn->query($sql);
        while ($row = $result->fetch_array(MYSQLI_ASSOC))
        {
            $delay =GetMinutes(actual_datetime(),convert_utm($row['Timestamp'],$row['preparedST']));

            $data = array(  'equipo'=>$row['equipo'],
			                'id_estacion'=>$row['estacion'],
			                'nombre'=>$row['nombre'], 
                            'sector'=>$row['sector'],
                            'timestamp'=>convert_utm($row['Timestamp'],$row['preparedST']),
                            'actualtime'=>actual_datetime(),
                            'delay_db'=>$row['config'],                           
                            'retraso'=>$delay,
                            'color'=>colorList($row['config'],$delay),
                            'marker'=>coloMarker($row['config'],$delay),
                            'letters'=>seconds2human($delay),
			                'icono'=>iconList($row['config'],$delay));
          
            
        }
        return $data;
    }
    function SensoresStation($id_estacion)
    {  
        global $conn;
        $data = array();
        $sql =" SELECT * FROM sensores a , estaciones_equipos b WHERE a.id_equipo=b.id_equipo AND b.id_estacion='".$id_estacion."' order by entrada  ";
        $result = $conn->query($sql);
        while ($row = $result->fetch_array(MYSQLI_ASSOC))
        {
            $data[] = $row;
        }       
        return $data;
    }
    function SelectSensor($estacion,$unidad)
    {
        global $conn;    
        $sql="SELECT SUBSTRING(DATA,(".$unidad.")*8+1,8) AS dato FROM ultimas_mediciones WHERE id_estacion ='".$estacion."'";       
        $result   = $conn->query($sql);
        while ($row = $result->fetch_array(MYSQLI_ASSOC))
        {
             $dato=$row['dato'];
        }
        return $dato;


    }
    function GetSymbol($unidad)
    {   
        global $conn;
        $unit = '';
        $sql ="select descripcion from def_unidades where id_unidad='".$unidad."'";
        $result   = $conn->query($sql);
        while ($row = $result->fetch_array(MYSQLI_ASSOC))
        {  
         
            $unit =utf8_encode($row['descripcion']);      
          
        }
        return $unit; 
    }

