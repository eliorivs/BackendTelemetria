<?php
    header('Content-type: application/json');
    require "conexion.php"; 
    require "conversions.php";
    require "credencials.php";

   

    $postdata = file_get_contents("php://input");
    if (isset($postdata)) {
        $request = json_decode($postdata);       
         $id_sector = $request->sector;       
         // $id_sector='4';
        if (($id_sector!=""))
	    {
             
         
            $sectores = getSectores();
	    //$id_sector='4';
            $reply =array('sectores'=>$sectores, 'estaciones'=>DevicesList($id_sector),'sector'=>GetNameSector($id_sector));
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

    function GetNameSector($id_sector)
    {

	global $conn; 
        $unit = '';
        $sql ="select nombre from sectores where id_sector='".$id_sector."'";
        $result   = $conn->query($sql);
        while ($row = $result->fetch_array(MYSQLI_ASSOC))
        {  
         
            $sector =utf8_encode($row['nombre']);      
          
        }
        return $sector; 

    }


  
  

    function getSectores()
    {
        global $conn;
        
        $data = array();
        $sql =" SELECT * FROM sectores ";
        $result = $conn->query($sql);
        while ($row = $result->fetch_array(MYSQLI_ASSOC))
        {
            $data []= array('id_sector'=>$row['id_sector'], 'nombre'=>$row['nombre']);
        }       
        return $data;
    }
 function DevicesList($id_sector)
    {
        global $conn;       
        $sql = "SELECT f.id_equipo as equipo,a.id_estacion as estacion,a.nombre,b.Timestamp_arrive as Timestamp, c.nombre AS sector, f.config, a.preparedST,c.id_sector,a.longitud,a.latitud from estaciones a, ultimas_mediciones b, sectores c , sectores_estaciones d  , estaciones_equipos e, equipos_configs f 
        WHERE a.id_estacion=b.id_estacion  AND a.id_estacion=d.id_estacion AND c.id_sector=d.id_sector AND a.id_estacion=e.id_estacion AND e.id_equipo=f.id_equipo and a.estado=1 and c.id_sector='".$id_sector."'";       
        $result = $conn->query($sql);
        while ($row = $result->fetch_array(MYSQLI_ASSOC))
        {
            $sensores =  SensoresStation($row['estacion']);
            $delay =GetMinutes(actual_datetime(),convert_utm($row['Timestamp'],$row['preparedST']));

            $data[] = array('equipo'=>$row['equipo'],
			    'id_estacion'=>$row['estacion'],
			    'nombre'=>$row['nombre'], 
                            'sector'=>$row['sector'],
                            'timestamp'=>convert_utm($row['Timestamp'],$row['preparedST']),
                            'actualtime'=>actual_datetime(),
                            'delay_db'=>$row['config'],                           
                            'retraso'=>$delay,
                            'color'=>colorList($row['config'],$delay),
                            'letters'=>seconds2human($delay),
			    'icono'=>iconList($row['config'],$delay),
			    'id_sector'=>$row['id_sector'],
			    'latitud'=>floatval($row['latitud']),
                            'longitud'=>floatval($row['longitud']),
			    'lecturas'=>GetLecturas($sensores,$row['estacion']));
          
            
        }
        return($data);
      // echo  json_encode($data, JSON_PRETTY_PRINT);

    }

    function sectorSelected($id_sector)
    {   
         global $conn;

         $sql = " SELECT a.id_estacion,a.nombre,a.preparedST, a.latitud,a.longitud,d.DATA,d.Timestamp,d.Timestamp_arrive, f.config  
                  FROM estaciones a , sectores b , sectores_estaciones c, ultimas_mediciones d, estaciones_equipos e, equipos_configs f 
                  WHERE a.id_estacion = c.id_estacion
                  AND b.id_sector=c.id_sector
                  AND c.id_sector='".$id_sector."'
                  AND a.id_estacion=d.id_estacion  
                  AND a.id_estacion=e.id_estacion 
                  AND e.id_equipo=f.id_equipo AND a.estado='1'";
         $result = $conn->query($sql);
         while ($row = $result->fetch_array(MYSQLI_ASSOC))
         {
            $sensores =  SensoresStation($row['id_estacion']);
            $delay =(GetMinutes(actual_datetime(),convert_utm($row['Timestamp'],$row['preparedST'])));
            $data []= array(
                            'id_estacion'=> $row['id_estacion'],
                            'nombre'=>$row['nombre'],
                            'latitud'=>$row['latitud'],
                            'longitud'=>$row['longitud'],
                            'data'=>$row['DATA'],
                            'timestamp'=>convert_utm($row['Timestamp'],$row['preparedST']),
                            'actualtime'=>actual_datetime(),
                            'arrive'=>$row['Timestamp_arrive'],
                            'delay_db'=>$row['config'],   
                            'retraso'=>$delay,
                            'color'=>colorList($row['config'],$delay),
                            'marker'=>coloMarker($row['config'],$delay),
                            'letters'=>seconds2human($delay),
			    'checked'=>'checked',
			    'icono'=>iconList($row['config'],$delay),
                            'lecturas'=> GetLecturas($sensores,$row['id_estacion']),
                            
                          
                            );
       
         }
         return $data;
       
    }
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
