<?php
    date_default_timezone_set('America/Santiago');
    header("Access-Control-Allow-Origin: *");
    header('Content-type: application/json');
    require "conexion.php"; 
    if (isset($_SERVER['HTTP_ORIGIN'])) {

    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    

    }

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");      

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers:{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);

    }

    $estaciones = DevicesList();



    print json_encode($estaciones,JSON_PRETTY_PRINT);

    function DevicesList()
    {
        global $conn;       
        $sql = "SELECT f.id_equipo as equipo,a.id_estacion as estacion,a.nombre,b.Timestamp_arrive as Timestamp, c.nombre AS sector, f.config, a.preparedST,c.id_sector from estaciones a, ultimas_mediciones b, sectores c , sectores_estaciones d  , estaciones_equipos e, equipos_configs f 
        WHERE a.id_estacion=b.id_estacion  AND a.id_estacion=d.id_estacion AND c.id_sector=d.id_sector AND a.id_estacion=e.id_estacion AND e.id_equipo=f.id_equipo and a.estado=1";       
        $result = $conn->query($sql);
        while ($row = $result->fetch_array(MYSQLI_ASSOC))
        {
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
			    'id_sector'=>$row['id_sector']);
          
            
        }
usort($data, 'sortBydelay');

        return($data);
      // echo  json_encode($data, JSON_PRETTY_PRINT);

    }
function sortBydelay($x, $y) {
    return $x['retraso'] - $y['retraso'];
}
    function colorList($delaydb,$delaydevice)
    {
     

        if($delaydevice<$delaydb)
	    {
             return 'success';
        }
        if($delaydevice<$delaydb*3)
        {
            return 'warning';
        }
        if($delaydevice>$delaydb*3)
	    {
       	    return 'danger';
        }
   }
 function iconList($delaydb,$delaydevice)
    {
     

        if($delaydevice<$delaydb)
	    {
             return 'checkmark-circle-outline';
        }
        if($delaydevice<$delaydb*3)
        {
            return 'warning-outline';
        }
        if($delaydevice>$delaydb*3)
	    {
           return 'alert-circle-outline';
        }
   }
       
     
         
       

   
    function actual_datetime()
    {
        /*$now         = new DateTime("now", new DateTimeZone('America/Santiago'));
        $actual_data = $now->format('Y-m-d H:i:s');*/

        $mifecha= date('Y-m-d H:i:s'); 
        $NuevaFecha = strtotime ( '-0 hour' , strtotime ($mifecha) ) ; 
        $NuevaFecha = date ( 'Y-m-d H:i:s' , $NuevaFecha); 
        return ($NuevaFecha);
    }
    function convert_utm($time,$ST){
    
   
        $calc = date_create_from_format('YmdHis.u',$time);
        if($ST=='1')
        {
            $date = date_format($calc, 'Y-m-d H:i:s');
            $NuevaFecha = strtotime ( '-4 hour' , strtotime ($date) ) ; 
            $date = date ( 'Y-m-d H:i:s' , $NuevaFecha); 

            //minus 4 hours
        }
 	if($ST=='2')
        {
            $date = date_format($calc, 'Y-m-d H:i:s');
            $NuevaFecha = strtotime ( '+1 hour' , strtotime ($date) ) ; 
            $date = date ( 'Y-m-d H:i:s' , $NuevaFecha); 

            //minus 4 hours
        }
        else
        {
            $date = date_format($calc, 'Y-m-d H:i:s');
        }
       
       // print $ST;
       
        return $date;
    }
  
    function GetMinutes($actual_data,$last_insert)
    {
    
            $workingHours = (strtotime($actual_data) - strtotime($last_insert));
            $minutes      = floor($workingHours / 60);
            return ($minutes);
    
    }
    function seconds2human($minutes) {
        $d = floor ($minutes / 1440);
        $h = floor (($minutes - $d * 1440) / 60);
        $m = $minutes - ($d * 1440) - ($h * 60);
        //$m = $minutes - ($d * 1440) ;// - ($h * 60);
        //
        // Then you can output it like so...
        //
        //echo "{$minutes}min converts to {$d}d {$h}h {$m}m";
        
        return "{$d}d, {$h}h {$m}m";
        }


?>

