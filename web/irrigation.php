<?php
	require("phpMQTT.php");
	echo "Starting...\n";

  use \Bluerhinos\phpMQTT as phpMQTT;

	$server = "cdavidcloud.gnd.upv.es";     // change if necessary
	$port = 1883;                     // change if necessary
	$username = "MQTT";                   // set your username
	$password = "Mqtt2020";                   // set your password
	$client_id = "irrgation-addon"; // make sure this is unique for connecting to sever - you could use uniqid()

	//$mqtt = new phpMQTT($server, $port, $client_id);

	while(true)
    {
	/*if ($mqtt->connect(true, NULL, $username, $password))
		{
		$mqtt->publish("bluerhinos/phpMQTT/examples/publishtest", "Hello World! at " . date("r"), 1);
		$mqtt->close();
		}
	else {echo "Time out!\n";}*/

    sleep(1);

    }


 ?>
