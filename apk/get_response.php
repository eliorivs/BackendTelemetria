<?php
error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");
header('Content-type: application/json');
require "conexion.php";

    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');   
    }


    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers:        
            {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }
  
    $postdata = file_get_contents("php://input");
    if (isset($postdata)) {
        $request = json_decode($postdata);
        $parametros = $request->parametros;
        $estaciones = $request->estaciones;
        $tiempo = $request->tiempo;
        
	/*$parametros = ['1'];
	$estaciones = ['23'];
	$tiempo=1;*/

        if (($parametros != "")&&($estaciones!="")&&($tiempo!="")) {
            //echo "Server returns: ok ";
           // $resolve =array('parametros'=>$parametros,'estaciones'=>$estaciones,'tiempo'=>$tiempo);
        //    print json_encode($resolve);
           $data =get_data_sensor($parametros,$estaciones, $tiempo);
           print json_encode($data);
     
        
          
        }
        else {
            echo "Empty username parameter!";
        }
    }
    else {
        echo "Not called properly with username parameter!";
    }


function preparedST($id_estacion)
 {
	 global $conn;
	 $sql = "select preparedST from estaciones where id_estacion='" . $id_estacion. "'";
         $result = $conn->query($sql);    
	 while ($row = $result->fetch_array(MYSQLI_ASSOC))
	 {
		
		$st = $row['preparedST'];
		 
	 }
         return $st;

 }

    
 

 function get_data_sensor($parametros,$estaciones, $tiempo)
{
    global $conn;
    $j = 0;
    $i = 0;
    $series = 0;
    $arr = array();
    $resultado = array();
    $j = 0;


   foreach ($parametros as $parametro){
 

    foreach ($estaciones as $estacion){

    $preparedST =  preparedST($estacion);

    $color = DeviceList($estacion);
    

    $max_time = maxtime_station($estacion);    
   // echo $max_time;
    $suffix_sql = secondtime_station($max_time,$tiempo); 
    //echo $suffix_sql;
    $dat =  info_serie($parametro, $estacion);
    //echo $dat ;
    //print json_encode($dat);
    $symbol = convert_unidad($dat['unidad']); 
    //echo $symbol;
     //print json_encode($symbol);
   
    unset($arr);
    $j = 0;

   $sql = "SELECT Timestamp as tiempo, SUBSTRING(DATA,(".$parametro.")*8+1,8) AS dato FROM msg_input WHERE Tipo='A' AND Timestamp is not null and DATA  is not null and id_estacion ='".$estacion."'".$suffix_sql." order by tiempo asc"; 

   // echo $sql;

    $result = $conn->query($sql);
    while ($row = $result->fetch_array(MYSQLI_ASSOC))
    {

        if ($j == 0)
        {
            $arr['name'] = $dat['descripcion']."-".GetName($estacion);
            $arr['lineWidth'] = '1.5';
            $arr['color'] = $color; 
            /*$arr['marker'] = array('enabled'=>'true');*/
           // $arr['dataGrouping'] = array('enabled'=>false,'forced'=>false);    
            //$arr['time'] = coment_time($tiempo);
            //$arr['sensor'] = 'Sensor de ' .get_sensor($parametro);
            //$arr['sensor_x'] = (get_sensor($parametro));          
            $arr['tooltip'] =$symbol;

             $j++;
        }
        $datatime = $row['tiempo'];
        $dato = $row['dato'];
        $arr['data'][] = [convert_times(convert_utms($datatime)),round(IEEE754To32Floati($dato) , 2)];
        //  $arr['data'][] = [convert_utmx(convert_utms($datatime),preparedST),round(IEEE754To32Floati($dato) , 2)];
        //$arr['data'][] = [convert_time(convert_utm($tiempo)),$dato ];

    }
    //print json_encode($arr);
    array_push($resultado, $arr);
    
    
    //return ($resultado);


    
    }
  }

   

  
    return $resultado;

}


function convert_utmx($time,$ST){
    
   
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



function convert_utms($time)
{

    $calc = date_create_from_format('YmdHis.u', $time);
    $date = date_format($calc, 'Y-m-d H:i:s');
    $IST = new DateTime($date, new DateTimeZone('UTC'));
    $IST->setTimezone(new DateTimeZone('-4'));
    $nueva_hora = $IST->format('YmdHis.u');
    return $nueva_hora;
}
function convert_times($mil)
{
  
    $calc = date_create_from_format('YmdHis.u', $mil);
    $date = date_format($calc, 'Y-m-d H:i:s');
    $convert = strtotime($date) * 1000;
    return $convert;
}

function info_serie($position, $id)
{
    global $conn;
    $sql = " SELECT a.unidad as unidad , a.desc_larga as descripcion  FROM sensores a , estaciones_equipos b WHERE a.id_equipo=b.id_equipo AND a.entrada='" . $position . "' AND b.id_estacion='" . $id . "'";
    $result = $conn->query($sql);  
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $unidad = $row['unidad'];
    $descripcion = $row['descripcion'];    
    $info = array('unidad'=>$unidad,'descripcion'=>$descripcion);
    return $info;
    

}
function convert_unidad($unidad)
{
    global $conn;
    $sql = "select  descripcion,decimals  from def_unidades where id_unidad='" . $unidad . "'";
    $result = $conn->query($sql);
    
     while ($row = $result->fetch_array(MYSQLI_ASSOC))
    {
         $symbol = utf8_encode($row['descripcion']);
         $decimals = $row['decimals'];
         
    }
   
    
    $data =array('valueSuffix'=>" ".$symbol,'valueDecimals'=>$decimals);
    //print json_encode($data);
    return $data;

}
function coment_time($tiempo)
{  
    if ($tiempo == '1')
    {
        $data = 'Últimas 24 horas';
    }
    if ($tiempo == '2')
    {
        $data = 'Últimos 7 días';
    }
    if ($tiempo == '3')
    {
        $data = 'Últimos 30 dias';
    }
    if ($tiempo == '4')
    {
        $data = 'Últimos 6 Meses';
    }
    if ($tiempo == '5')
    {
        $data = 'Todo';
    }
    return $data;

}
function get_sensor($parametro)
{
    global $conn;
    $sql = "SELECT A.descripcion AS descripcion FROM def_tipo_medida A,sensores B WHERE A.id_tipo_medida=B.tipo_medida AND B.entrada='" . $parametro . "' LIMIT 1 ";
    $result = $conn->query($sql);
    while ($row = $result->fetch_array(MYSQLI_ASSOC))
    {
        $sensor = utf8_encode($row['descripcion']);
    }
    return $sensor;

}

function maxtime_station($id_station)
{
    global $conn;
    $sql    = " SELECT max(Timestamp) as max_time FROM msg_input WHERE id_estacion ='" . $id_station . "'";
    $result = $conn->query($sql);
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $max_time = $row['max_time']; 
    return ($max_time);
}
function secondtime_station($max_time,$tiempo)
{   $sentence='';
     
    if ($tiempo == '1')
    {   
        $argument = '- 1 day';
        $data = convert_utc_arguments($argument,$max_time);
        $sentence =" and Timestamp BETWEEN '".$data."' AND '".$max_time."'";       

    }
     if ($tiempo == '2')
    {   
        $argument = '- 1 week';
        $data = convert_utc_arguments($argument,$max_time);
        $sentence =" and Timestamp BETWEEN '".$data."' AND '".$max_time."'";       

    }
    if ($tiempo == '3')    {
       

        $argument = '- 1 month'; 
        $data = convert_utc_arguments($argument,$max_time);
        $sentence =" and Timestamp BETWEEN '".$data."' AND '".$max_time."'";

    }
    if ($tiempo == '4')
    {
       
        $argument = '- 6 month'; 
        $data = convert_utc_arguments($argument,$max_time);
        $sentence =" and Timestamp BETWEEN '".$data."' AND '".$max_time."'";
      
    }
    if ($tiempo == '5')
    {
       $sentence ='';
    }
    return $sentence;
}
function convert_utc_times($time){
    
   
    $calc = date_create_from_format('YmdHis.u',$time);
    $date = date_format($calc, 'Y-m-d H:i:s');
    $IST = new DateTime($date, new DateTimeZone('UTC'));
    $IST->setTimezone(new DateTimeZone('-3'));
    $nueva_hora = $IST->format('YmdHis.u');
    return $nueva_hora;
}
function convert_utc_arguments($argument,$max_time)
{
        $max_time =convert_utc_times($max_time);
        $calc = date_create_from_format('YmdHis.u',$max_time);
        $date = date_format($calc, 'Y-m-d H:i:s');
        $date = date("Y-m-d H:i:s",strtotime($date.$argument));
        $IST = new DateTime($date, new DateTimeZone('UTC'));
        $IST->setTimezone(new DateTimeZone('-3'));    
        $nueva_hora = $IST->format('YmdHis.u');
        return $nueva_hora;

}

function IEEE754To32Floati($strHex) {
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
function GetName($eid)
{	
	global $conn;
    $sql    = " select nombre from estaciones where id_estacion ='".$eid."'";
    $result = $conn->query($sql);
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $nombre = $row['nombre']; 
    return ($nombre);
}
function colors()
{
    $background_colors = array('navy', 'orange', 'red', 'purple', '#FF3838','#FFA500','#007F2D','#8F1989','#1016E5','#663300','#00B5FF','#FF0000','#ED7D31');
    $randomNumber = rand(0,12);
    #return($background_colors[$randomNumber]);
    return('#2DD36F');
}
 function DeviceList($id_estacion)
    {
        global $conn;       
        $sql = "SELECT f.id_equipo as equipo,a.id_estacion as estacion,a.phonenumber as phone,a.nombre,b.Timestamp_arrive as Timestamp,c.id_sector, c.nombre AS sector, f.config, a.preparedST 
        FROM estaciones a, ultimas_mediciones b, sectores c , sectores_estaciones d  , estaciones_equipos e, equipos_configs f 
        WHERE a.id_estacion=b.id_estacion  AND a.id_estacion=d.id_estacion AND c.id_sector=d.id_sector AND a.id_estacion=e.id_estacion AND e.id_equipo=f.id_equipo and a.id_estacion='".$id_estacion."'";   
 	#print ($sql);    
        $result = $conn->query($sql);
        while ($row = $result->fetch_array(MYSQLI_ASSOC))
        {   
            $delay = GetMinutes(actual_datetime(),convert_utm($row['Timestamp'],$row['preparedST']));

           
          
            
        }

        return (colorSerie($row['config'],$delay));
    }
function colorSerie($delaydb,$delaydevice)
{
     

        if($delaydevice<$delaydb)
	{
             return '#2dd36f';
        }
        if($delaydevice<$delaydb*3)
        {
            return '#2dd36f';
        }
        if($delaydevice>$delaydb*3)
	{
       	    return '#2dd36f';
        }
 }
  function actual_datetime()
    {
       

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






    



?>






    
    
    
    
    
