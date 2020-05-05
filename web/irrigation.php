<?php
	require("phpMQTT.php");
	echo "Starting...\n";

	$server = "hassio";     // change if necessary
	$port = 1883;                     // change if necessary
	$username = "mqtt";                   // set your username
	$password = "Mqtt2020";                   // set your password
	$client_id = "irrgation-addon-configure"; // make sure this is unique for connecting to sever - you could use uniqid()

	$mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);

	if ($mqtt->connect(true, NULL, $username, $password))
		{
		$mqtt->publish("TEST", "Hello World! at " . date("r"), 1);
		$mqtt->close();
		}
	else {echo "Time out!\n";}


 ?>
