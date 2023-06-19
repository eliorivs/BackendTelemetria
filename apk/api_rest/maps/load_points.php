<?php

include_once ('conexion.php');
include_once ('calculate_delay.php');
include_once ('conversions.php');
$user_is_admin = 1;
$user_id=3;



 $sql = "SELECT  
                            s.*
                            ,AVG(es.latitud) AS latitud
                            ,AVG(es.longitud) AS longitud
                    FROM 
                            sectores s
                            INNER JOIN sectores_estaciones se ON s.id_sector = se.id_sector
                            INNER JOIN estaciones es ON se.id_estacion = es.id_estacion
              " . (($user_is_admin) ? "" : "INNER JOIN estaciones_usuarios eu ON (eu.id_usuario=$user_id AND eu.id_estacion = es.id_estacion )") . "
                 GROUP BY s.id_sector;";

                 echo $sql;

/*
 $sql = "
                    SELECT 
                            es.nombre as nombre_estacion
                            , es.id_estacion
                            , es.ubicacion
                            , es.utm_este
                            , es.utm_norte
                            , es.img
                            , es.conexion as es_conexion
                            , es.prof_pozo
                            , es.prof_sonda
                            , es.modelo_sensor
                            , es.ns_sensor
                            , es.longitud
                            , es.latitud
                            , se.id_sector 
                            , se.nombre as nombre_sector
                            , eq.desc_corta as equipo
                            , eq.id_equipo
                            , eq.IdGW
                            , dtm.descripcion as sensor
                            , du.descripcion as unidad
                            , (SELECT timestamp(mi.timestamp_arrive) FROM ultimas_mediciones mi WHERE mi.id_estacion = es.id_estacion AND mi.Tipo = 'A' ORDER BY mi.id_msg DESC LIMIT 1 ) as ultima_fecha
                            , SUBSTRING((SELECT mi.DATA FROM ultimas_mediciones mi WHERE mi.id_estacion = es.id_estacion AND mi.Tipo = 'A' ORDER BY mi.id_msg DESC LIMIT 1 ),s.entrada*8+1,8) as ultima_medicion
                            , s.factor
                            , s.offset
                            , s.tasa_muestreo
                            , s.id_sensor
                            , s.entrada as pos_sensor
                            , a_umbral.tipo as umbral
                            , a_lectura.tipo as lectura
                            , a_desconexion.tipo as desconexion
                            , a_desconexion.estado 
                            
                    FROM 
                            equipos eq
                            INNER JOIN estaciones_equipos es_eq ON (eq.id_equipo = es_eq.id_equipo )    
                            INNER JOIN estaciones es ON ( es.id_estacion = es_eq.id_estacion)
                            INNER JOIN sectores_estaciones see ON (es.id_estacion=see.id_estacion)
                            INNER JOIN sectores se ON (se.id_sector=see.id_sector)
                            INNER JOIN sensores s ON ( s.id_equipo = eq.id_equipo AND s.habilitado = 1)
                            INNER JOIN def_tipo_medida dtm ON ( s.tipo_medida = dtm.id_tipo_medida )
                            INNER JOIN def_unidades du ON ( s.unidad = du.id_unidad )

                            " . (($user_is_admin) ? "" : "INNER JOIN estaciones_usuarios eu ON (eu.id_usuario=$user_id AND eu.id_estacion = es.id_estacion )") . "
                            
                            LEFT JOIN alarmas a_umbral ON ( es.id_estacion = a_umbral.id_estacion AND a_umbral.id_equipo = eq.id_equipo AND a_umbral.id_sensor = s.id_sensor AND a_umbral.estado = 'ACTIVA' AND a_umbral.tipo='UMBRAL')
                            LEFT JOIN alarmas a_lectura ON ( es.id_estacion = a_lectura.id_estacion AND a_lectura.id_equipo = eq.id_equipo AND a_lectura.id_sensor = s.id_sensor AND a_lectura.estado = 'ACTIVA' AND a_lectura.tipo='LECTURA')
                            LEFT JOIN alarmas a_desconexion ON ( es.id_estacion = a_desconexion.id_estacion AND a_desconexion.id_equipo = eq.id_equipo  AND a_desconexion.estado = 'ACTIVA' AND a_desconexion.tipo='DESCONEXION' )
                    WHERE
                            eq.habilitado = 1
                    ORDER BY eq.id_equipo ASC, s.id_sensor ASC";*/

                    echo $sql;
 


//$markers = get_markers_map();

//print json_encode($markers);

function get_markers_map()
  {
    $i = 0;
    global $conn;

    $sql    = "SELECT c.id_estacion AS estacion, s.nombre AS sector ,c.nombre AS nombre, c.estado as estado, b.id_sector as sector, c.latitud as latitud, c.longitud as longitud FROM sectores_estaciones b,  estaciones c, sectores s WHERE b.id_estacion=c.id_estacion AND  s.id_sector=b.id_sector and c.estado='1' and b.id_sector='4' ";
    $result = $conn->query($sql);
   
    while ($row = mysqli_fetch_array($result))
      {
        $sector        = $row['sector'];
        $estado        = $row['estado'];
        $estacion      = $row['estacion'];
        $name_estacion = $row['nombre'];
        $latitud =  $row['latitud'];
        $longitud  = $row['longitud'];
        $arr=intval($estacion) ;
        
        $map[]        = array(
            'sector' => $sector,
            'estacion' => $estacion,
            'name_estacion' => $name_estacion,            
            'latitud'=> $latitud,
            'longitud'=> $longitud,
            /*'lecturas'=> GetLastSensor($estacion),*/
            'delay'=>  get_delay(intval($estacion)),
            'estado'=>GetEstado($estacion),
            'alarma'=>GetAlarms($estacion)

        );
      }
  return $map;
   
  }
  function GetLastSensor($id)
  {
    global $conn;

    $data = [];
  
    $sql="SELECT  a.entrada as entrada, a.unidad as description from  sensores a, estaciones_equipos b WHERE b.id_equipo = a.id_equipo AND b.id_estacion='".$id."' ";
     $result = $conn->query($sql);
      while ($row = mysqli_fetch_array($result))
      {
        $entrada = $row['entrada'];
        $description = $row['description'];
        $data[] = GetSymbol($description,$id,$entrada);
      }
      return $data;

  }

function GetSymbol($unidad,$estacion,$entrada)
{   
    global $conn;
    $sql ="select  descripcion from def_unidades where id_unidad='".$unidad."'";
    $result   = $conn->query($sql);
     while ($row = $result->fetch_array(MYSQLI_ASSOC))
     {  

       // $date = SelectMaxTime($estacion);//
        $value = round(IEEE754To32Float(SelectSensor($estacion,$entrada)) , 2);
        $unit =utf8_encode($row['descripcion']);      
        $data = $value.' ['.$unit.']'."<br>";
    }
     return $data;
 
}

Function SelectSensor($estacion,$unidad)
{
 global $conn;
   
   $sql="SELECT SUBSTRING(DATA,(".$unidad.")*8+1,8) AS dato FROM ultimas_mediciones WHERE id_estacion ='".$estacion."'";

   //echo $sql;
   $result   = $conn->query($sql);
   while ($row = $result->fetch_array(MYSQLI_ASSOC))
     {
      $dato= $row['dato'];
     }
     return $dato;


}


function GetAlarms($estacion)
{

  global $conn;
  $sql ="select count(*) as active from alarmas where id_estacion='".$estacion."' and estado ='ACTIVA'";
  $result   = $conn->query($sql);
   while ($row = $result->fetch_array(MYSQLI_ASSOC))
     {
      $active= $row['active'];
     }
     return $active;

}
function GetEstado($estacion)
{  
    global $conn;
    $alarma ='';
    $alarmas = GetAlarms($estacion);
    
    if($alarmas==0)
    {
        $alarma='OK';
    }
    else
    {  
         $sql ="select max(timestamp_inicio) as active , tipo from alarmas where id_estacion='".$estacion."' and estado ='ACTIVA' GROUP by tipo";
         $result   = $conn->query($sql);
         while ($row = $result->fetch_array(MYSQLI_ASSOC))
         {
           $alarm_type= $row['tipo'];
         }
         
         /******* *********/
         $sql ="SELECT  B.desc_corta as descripcion  FROM alarmas A, sensores B WHERE A.estado='ACTIVA' and A.id_sensor=B.id_sensor and A.id_estacion='1'";
         $result   = $conn->query($sql);
         while ($row = $result->fetch_array(MYSQLI_ASSOC))
         {
           //$sensrs[]=$row;
           $sensrs .='<br>'.'<i class="fas fa-circle" style="color:#ff786c"></i>  '.$row['descripcion']; 
         }
          
         if($alarma!='UMBRAL'){
             $alarma="<br>".$alarm_type;
         }
         else{
              $alarma="Alarma: <br>".$alarm_type.$sensrs;
         }
       
         
       
    }
    return ($alarma);
    
}



?>