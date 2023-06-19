<?php

function get_delay($id_station)
{
  
    $max_time= MaxTime_delay($id_station);

    if (empty($max_time)){
     return ('-');
    }
     else{
     $delay = calculate_delay(convert_utm(MaxTime_delay($id_station)), actual_datetime());
    //return ($delay);
     return($delay);
    }


}

function MaxTime_delay($id_station)
{
    global $conn;
    $sql    = " SELECT Timestamp_arrive as max_time FROM ultimas_mediciones WHERE id_estacion ='" . $id_station . "'";
    $result = $conn->query($sql);
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $max_time = $row['max_time'];
    }
    return ($max_time);
}


function convert_utm($time){
    
  
    $calc = date_create_from_format('YmdHis.u',$time);
    $date = date_format($calc, 'Y-m-d H:i:s');
    $IST = new DateTime($date, new DateTimeZone('UTC'));
    //$IST->setTimezone(new DateTimeZone('-3'));
    $IST->setTimezone(new DateTimeZone('-4'));
    //$nueva_hora = $IST->format('YmdHis.u');
    $nueva_hora = $IST->format('Y-m-d H:i:s');
    return $nueva_hora;
}



function convert_utc($time)
{
    if ($time == '') {
        return (0);
    } else {
        $calc = date_create_from_format('YmdHis.u', $time);
        $date = date_format($calc, 'Y-m-d H:i:s');
        $IST  = new DateTime($date, new DateTimeZone('America/Santiago'));
        //$IST->setTimezone(new DateTimeZone('-3'));
        $nueva_hora = $IST->format('Y-m-d H:i:s');
        return $nueva_hora;
    }
}
function actual_datetime()
{
    $now         = new DateTime("now", new DateTimeZone('America/Santiago'));
    $actual_data = $now->format('Y-m-d H:i:s');
    return ($actual_data);
}


function calculate_delay($last_insert, $actual_data)
{
    if ($last_insert == '') {
        return ('No data');
    } else {
        $workingHours = (strtotime($actual_data) - strtotime($last_insert));
        $minutes      = floor($workingHours / 60);
        return ($minutes);
    }
}




?>
