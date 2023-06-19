<?php

function IEEE754To32Float($strHex) {
    $v = hexdec($strHex);
    $x = ($v & ((1 << 23) - 1)) + (1 << 23);
    $exp = ($v >> 23 & 0xFF) - 127;
    return $x * pow(2, $exp - 23)*(($v >> 31)?-1:1);
}


function IEEE754To32Float2($strHex) {
	if(!strcmp($strHex,"00000000"))
		return 0;
		
	$binary = str_pad(base_convert($strHex, 16, 2), 32, "0", STR_PAD_LEFT);
	$sign = $binary[0];
	$exponent = bindec(substr($binary, 1, 8)) - 127;
	$mantissa = (2 << 22) + bindec(substr($binary, 9, 23));
	$floatVal = $mantissa * pow(2, $exponent - 23) * ($sign ? -1 : 1);

	return $floatVal;
}

function colorSerie($delaydb,$delaydevice)
    {
     

        if($delaydevice<$delaydb)
	    {
             return 'green';
        }
        if($delaydevice<$delaydb*3)
        {
            return 'yellow';
        }
        if($delaydevice>$delaydb*3)
	    {
       	    return 'red';
        }
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


   function coloMarker($delaydb,$delaydevice)
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


   function iconList($delaydb,$delaydevice)
    {
     

        if($delaydevice<$delaydb)
	    {
             return 'marker_green.jpg';
        }
        if($delaydevice<$delaydb*3)
        {
            return 'marker_orange.jpg';
        }
        if($delaydevice>$delaydb*3)
	    {
           return 'marker_red.jpg';
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
    function convert_utms($time,$ST){
    
   
            $calc = date_create_from_format('YmdHis.u',$time);    
            $date = date_format($calc, 'Y-m-d H:i:s');
            $NuevaFecha = strtotime ( '+4 hour' , strtotime ($date) ) ; 
            $date = date ( 'Y-m-d H:i:s' , $NuevaFecha); 

       
       
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
        //
        // Then you can output it like so...
        //
        //echo "{$minutes}min converts to {$d}d {$h}h {$m}m";
        
        return "{$d}d, {$h}h {$m}m";
        }


?>
