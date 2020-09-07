<?php

include_once("conf.php");

	$curlSES=curl_init(); 
	curl_setopt($curlSES,CURLOPT_URL,"$HASSIO_URL/services/mqtt");
	curl_setopt($curlSES,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curlSES, CURLOPT_HTTPHEADER, array(
		'content-type: application/json',
		"Authorization: Bearer $SUPERVISOR_TOKEN"
	));
	
	$result = json_decode( curl_exec($curlSES));
	curl_close($curlSES);
	
	print_r ($result);
	
?>