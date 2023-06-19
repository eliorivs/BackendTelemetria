<?php

header('Content-type: application/json');
include_once ('conexion.php'); 

getEstaciones(3,1);



function getEstaciones($user_id, $user_is_admin2) {
        // create a database connection, using the constants from config/db.php (which we loaded in estaciones.php)
      global $conn;

            // query a la db, obtenemos la información de las estaciones asociadas al usuario actual
            $sql = "
                    SELECT 
                            es.nombre as nombre_estacion
                            , es.id_estacion
                            , es.ubicacion
                            , es.utm_este
 			                , es.img
                            , es.utm_norte
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
                            , (SELECT timestamp(mi.Timestamp_arrive) FROM ultimas_mediciones mi WHERE mi.id_estacion = es.id_estacion AND mi.Tipo = 'A' ORDER BY mi.id_msg DESC LIMIT 1 ) as ultima_fecha
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

                            " . (($user_is_admin2) ? "" : "INNER JOIN estaciones_usuarios eu ON (eu.id_usuario=$user_id AND eu.id_estacion = es.id_estacion )") . "
                            
                            LEFT JOIN alarmas a_umbral ON ( es.id_estacion = a_umbral.id_estacion AND a_umbral.id_equipo = eq.id_equipo AND a_umbral.id_sensor = s.id_sensor AND a_umbral.estado = 'ACTIVA' AND a_umbral.tipo='UMBRAL')
                            LEFT JOIN alarmas a_lectura ON ( es.id_estacion = a_lectura.id_estacion AND a_lectura.id_equipo = eq.id_equipo AND a_lectura.id_sensor = s.id_sensor AND a_lectura.estado = 'ACTIVA' AND a_lectura.tipo='LECTURA')
                            LEFT JOIN alarmas a_desconexion ON ( es.id_estacion = a_desconexion.id_estacion AND a_desconexion.id_equipo = eq.id_equipo  AND a_desconexion.estado = 'ACTIVA' AND a_desconexion.tipo='DESCONEXION' )
                    WHERE
                            eq.habilitado = 1 AND se.id_sector ='4'
                    ORDER BY eq.id_equipo ASC, s.id_sensor ASC

			";
               
               //echo $sql;
            // query a la db, obtenemos la información de las estaciones asociadas a los sectores


            $result = $conn->query($sql);

          

            $res = array();
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $res[] = $row;
            }
           // $this->datos=$res;
           // return;
            $ne = 0;

            $total =$result->num_rows;

            $j = 0;




          


            for($i=0; $i<$total; $i++){
                $datos[$j]=array();               
                $datos[$j]['nombre_estacion']=($res[$i]['nombre_estacion']);
                $datos[$j]['latitud']=$res[$i]['latitud'];
                $datos[$j]['longitud']=$res[$i]['longitud'];
                $datos[$j]['id_estacion'] = $res[$i]['id_estacion'];
                $datos[$j]['ubicacion'] = ($res[$i]['ubicacion']);
                $datos[$j]['nombre_sector'] = ($res[$i]['nombre_sector']);
               // $this->datos[$j]['delay'] = floor((time() -  strtotime($res[$i]['ultima_fecha']) - TIMEZONE_CORRECT * 3600)/ 60) . '\'';

               $lastdate = $res[$i]['ultima_fecha'];

               //echo $lastdate;

               $createformat = date_create_from_format('Y-m-d H:i:s.u',$lastdate);
               $date = date_format($createformat, 'Y-m-d H:i:s');
               $dt = new DateTime($date, new DateTimeZone('+4'));
               // change the timezone of the object without changing it's time 
               $dt->setTimezone(new DateTimeZone('UTC'));
               // format the datetime
               $ultima = $dt->format('Y-m-d H:i:s');
               $now = new DateTime("now", new DateTimeZone('-4'));
               $actual_data = $now->format('Y-m-d, H:i:s'); ;
               $workingHours = (strtotime($actual_data) - strtotime($ultima));
               $minutes = floor($workingHours / 60);
               if($minutes < 0)
               { $response ="Sin Datos";}
               else
               { $response =''.$minutes."' ".''; }

               $datos[$j]['delay']=' '.$response;
               $datos[$j]['id_equipo'] = $res[$i]['id_equipo'];
               $datos[$j]['id_sector']=$res[$i]['id_sector'];
               $datos[$j]['desconexion']=$res[$i]['desconexion'];
               $datos[$j]['prof_pozo']=$res[$i]['prof_sonda'];
       	       $datos[$j]['prof_sonda']=$res[$i]['prof_sonda'];
	           $datos[$j]['modelo_sensor']=$res[$i]['modelo_sensor'];
	           $datos[$j]['ns_sensor']=$res[$i]['ns_sensor'];



                $k = 0;

                $datos[$j]['sensores'] = array();
                $datos[$j]['sensores'][$k] = array();

                $datos[$j]['sensores'][$k]['sensor'] = ($res[$i]['sensor']);
                $datos[$j]['sensores'][$k]['medicion'] = (round(IEEE754To32Float($res[$i]['ultima_medicion']) / $res[$i]['factor'] + $res[$i]['offset'], 3) . ' [' . $res[$i]['unidad'] . ']');
                $datos[$j]['sensores'][$k]['unidad'] = ($res[$i]['unidad']);
                $datos[$j]['sensores'][$k]['umbral'] = ($res[$i]['umbral']);
                $datos[$j]['sensores'][$k]['lectura'] = ($res[$i]['lectura']);
                
                
              //  $this->datos[$j]['id_equipo'] = $res['id_equipo'][$i]['id_equipo'];
                

                while ($i + 1 < $total && $res[$i]['id_equipo'] == $res[$i + 1]['id_equipo']) {
                    $k++;
                    $datos[$j]['sensores'][$k] = array();
                    $datos[$j]['sensores'][$k]['sensor'] = ($res[$i+1]['sensor']);
                    $datos[$j]['sensores'][$k]['medicion'] = (round(IEEE754To32Float2i($res[$i+1]['ultima_medicion']) / $res[$i+1]['factor'] + $res[$i+1]['offset'], 3) . ' [' . $res[$i+1]['unidad'] . ']');
                    $datos[$j]['sensores'][$k]['unidad'] = ($res[$i+1]['unidad']);
                    $datos[$j]['sensores'][$k]['umbral'] = ($res[$i+1]['umbral']);
                    $datos[$j]['sensores'][$k]['lectura'] = ($res[$i+1]['lectura']);
               
                 //   $this->datos[$j]['sensores'][$k]['lpip_precio'] = $res['data'][$i + 1]['lpip_precio'];
                    $i++;
                }
                $j++;


            }

    
          
      $datajson = array();    
      $estado =[];     


      for($k=0;$k<count($datos);$k++){
        
         $data = $datos[$k]['sensores'];
          unset($dat);
          for($i=0;$i<count($data);$i++)
          {
          	//$dat[]= utf8_encode($data[$i]['medicion']);
          	$dat[]= array('medicion'=> utf8_encode($data[$i]['medicion']),'umbral'=>$data[$i]['umbral'],'sensor'=>utf8_encode($data[$i]['sensor']));
          }
          array_push($datajson, $dat);
      }
     
      for($i=0;$i<count($datos);$i++){

      	$estado[] = array('nombre'=>$datos[$i]['nombre_estacion'],'latitud'=>$datos[$i]['latitud'],'desconexion'=> $datos[$i]['desconexion'],'longitud'=>$datos[$i]['longitud'] ,'delay'=>$datos[$i]['delay'], 'mediciones'=> $datajson[$i]);

      }
       echo json_encode($estado);
          
       
    }

function IEEE754To32Float($strHex) {
    $v = hexdec($strHex);
    $x = ($v & ((1 << 23) - 1)) + (1 << 23);
    $exp = ($v >> 23 & 0xFF) - 127;
    return $x * pow(2, $exp - 23)*(($v >> 31)?-1:1);
}


function IEEE754To32Float2i($strHex) {
	if(!strcmp($strHex,"00000000"))
		return 0;
		
	$binary = str_pad(base_convert($strHex, 16, 2), 32, "0", STR_PAD_LEFT);
	$sign = $binary[0];
	$exponent = bindec(substr($binary, 1, 8)) - 127;
	$mantissa = (2 << 22) + bindec(substr($binary, 9, 23));
	$floatVal = $mantissa * pow(2, $exponent - 23) * ($sign ? -1 : 1);

	return $floatVal;
}





?>