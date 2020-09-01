<?php
	//include
	require("phpMQTT.php");
	include_once("lib.php");
	
	// Set necessary headers
	header("Content-Type: text/event-stream");
	header("Cache-Control: no-cache");
	header("Connection: keep-alive");
	header("Access-Control-Allow-Origin: *");	
	
	// date time variant
	$DATETIME=0;
	$EVENT_PRE=array();
	$EVENT=array();

	// infinite loop
	while (1)
			{
			// Set Max execution time for this loop to 5s
			set_time_limit(10);

			//GET SENSORS
			/*$curlSES=curl_init(); 
			curl_setopt($curlSES,CURLOPT_URL,"$HASSIO_URL/states");
			curl_setopt($curlSES,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($curlSES, CURLOPT_HTTPHEADER, array(
				'content-type: application/json',
				"Authorization: Bearer $SUPERVISOR_TOKEN"
				));
	
			$result_json=curl_exec($curlSES);
			$result = json_decode($result_json);
			curl_close($curlSES);
			
			echo $result_json;*/
	
			$sensors=get_sensor_list();
			
			foreach ($sensors as $sensor)
				{
				foreach ($sensors_friendly_name as $sensor_friendly_name)
					if ($sensor->friendly_name==$sensor_friendly_name) $EVENT[$sensor_friendly_name]=$sensor->state;
				}
			
			// Execute every second
			$diff=array_diff_assoc_recursive($EVENT,$EVENT_PRE);

			// If any diference in new event
			if (!empty($diff))
					{
					echo "data: ".json_encode ($EVENT)."\n\n";
					ob_flush();
					flush();
					}
			
			// store EVENT in EVENT_PRE			
            $EVENT_PRE=$EVENT;  
			
			// wait 500ms
			usleep(100000);			
			}
			

 ?>
