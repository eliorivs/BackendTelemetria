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

?>