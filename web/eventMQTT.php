<?php
	//include
	require("phpMQTT.php");
	include_once("lib.php");
	
	// Set necessary headers
	header("Content-Type: text/event-stream");
	header("Cache-Control: no-cache");
	header("Connection: keep-alive");
	header("Access-Control-Allow-Origin: *");	

	// mqtt config
	$qos=0;
	$client_id 	= "irrigation-addon-event-mqtt"; 

	// mqtt object
	$mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);	

	// Connect to server 
	if (!$mqtt->connect(true, NULL, $username, $password)) {echo "Time out!\n";}

	// Subscribe to topic
	$mqtt->subscribe([$topic => ['qos' => $qos,'function' => '__direct_return_message__']]);
	
	// date time variant
	$DATETIME=0;
	$EVENT_PRE=array();
	$EVENT=array();

	// infinite loop
	while (1)
			{
			// Set Max execution time for this loop to 5s
			set_time_limit(5);
	
			// Check new message from mqtt
			$message = $mqtt->proc();
			
			// if new message
			if ($message !== true) $EVENT=json_decode($message,true);
			
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
			
			// wait 1ms
			usleep(1000);			
			}
			

 ?>
