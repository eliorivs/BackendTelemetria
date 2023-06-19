SELECT s.* ,AVG(es.latitud) AS latitud ,AVG(es.longitud) AS longitud FROM sectores s INNER JOIN sectores_estaciones se ON s.id_sector = se.id_sector INNER JOIN estaciones es ON se.id_estacion = es.id_estacion GROUP BY s.id_sector;


$user_is_admin2 = 1;
$user_id=3;
  $sql = "SELECT  
                            s.*
                            ,AVG(es.latitud) AS latitud
                            ,AVG(es.longitud) AS longitud
                    FROM 
                            sectores s
                            INNER JOIN sectores_estaciones se ON s.id_sector = se.id_sector
                            INNER JOIN estaciones es ON se.id_estacion = es.id_estacion
              " . (($user_is_admin2) ? "" : "INNER JOIN estaciones_usuarios eu ON (eu.id_usuario=$user_id AND eu.id_estacion = es.id_estacion )") . "
                 GROUP BY s.id_sector;";

                 echo $sql;


                 /**********************/



                 SELECT es.nombre as nombre_estacion , es.id_estacion , es.ubicacion , es.utm_este , es.utm_norte , es.img , es.conexion as es_conexion , es.prof_pozo , es.prof_sonda , es.modelo_sensor , es.ns_sensor , es.longitud , es.latitud , se.id_sector , se.nombre as nombre_sector , eq.desc_corta as equipo , eq.id_equipo , eq.IdGW , dtm.descripcion as sensor , du.descripcion as unidad , (SELECT timestamp(mi.timestamp_arrive) FROM ultimas_mediciones mi WHERE mi.id_estacion = es.id_estacion AND mi.Tipo = 'A' ORDER BY mi.id_msg DESC LIMIT 1 ) as ultima_fecha , SUBSTRING((SELECT mi.DATA FROM ultimas_mediciones mi WHERE mi.id_estacion = es.id_estacion AND mi.Tipo = 'A' ORDER BY mi.id_msg DESC LIMIT 1 ),s.entrada*8+1,8) as ultima_medicion , s.factor , s.offset , s.tasa_muestreo , s.id_sensor , s.entrada as pos_sensor , a_umbral.tipo as umbral , a_lectura.tipo as lectura , a_desconexion.tipo as desconexion , a_desconexion.estado FROM equipos eq INNER JOIN estaciones_equipos es_eq ON (eq.id_equipo = es_eq.id_equipo ) INNER JOIN estaciones es ON ( es.id_estacion = es_eq.id_estacion) INNER JOIN sectores_estaciones see ON (es.id_estacion=see.id_estacion) INNER JOIN sectores se ON (se.id_sector=see.id_sector) INNER JOIN sensores s ON ( s.id_equipo = eq.id_equipo AND s.habilitado = 1) INNER JOIN def_tipo_medida dtm ON ( s.tipo_medida = dtm.id_tipo_medida ) INNER JOIN def_unidades du ON ( s.unidad = du.id_unidad ) LEFT JOIN alarmas a_umbral ON ( es.id_estacion = a_umbral.id_estacion AND a_umbral.id_equipo = eq.id_equipo AND a_umbral.id_sensor = s.id_sensor AND a_umbral.estado = 'ACTIVA' AND a_umbral.tipo='UMBRAL') LEFT JOIN alarmas a_lectura ON ( es.id_estacion = a_lectura.id_estacion AND a_lectura.id_equipo = eq.id_equipo AND a_lectura.id_sensor = s.id_sensor AND a_lectura.estado = 'ACTIVA' AND a_lectura.tipo='LECTURA') LEFT JOIN alarmas a_desconexion ON ( es.id_estacion = a_desconexion.id_estacion AND a_desconexion.id_equipo = eq.id_equipo AND a_desconexion.estado = 'ACTIVA' AND a_desconexion.tipo='DESCONEXION' ) WHERE eq.habilitado = 1 AND   se.id_sector ='4' ORDER BY eq.id_equipo ASC, s.id_sensor ASC