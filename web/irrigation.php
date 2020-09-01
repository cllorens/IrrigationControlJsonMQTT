<?php
	require("phpMQTT.php");
	include_once("lib.php");

	echo "START SETUP\n";
	// Connect to server
	$client_id = "irrgation-addon-configure";
	$mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);
	

	// date time variant
	$CONFIG=array();
	$NEWCONFIG=array();

	if ($mqtt->connect(true, NULL, $username, $password))
		{
		echo "LOGED IN\n";

		//Store datetime for timeout
		$DATETIME=strtotime("now");

		// Subscribe to topic
		$mqtt->subscribe([$topic => ['qos' => $qos,'function' => '__direct_return_message__']]);
		
		do
			{
			// Check new message from mqtt
			$message = $mqtt->proc();
			
			// if new message
			if ($message !== true) $CONFIG=json_decode($message,true);
			}while(empty($CONFIG) && (strtotime("now")-$DATETIME)<10);
			
		if (empty($CONFIG)) echo "NO OLD CONFIG RECOVERED\n";
		else echo "OLD CONFIG RECOVERED\n";
				
		// GENERATE ALL SCHEDULES
		for ($s=0;$s<$NumSchedules;$s++)
			{
			// 
			if ($clear) $NEWCONFIG[$s]["OUT"]=0;
			else $NEWCONFIG[$s]["OUT"]=(isset($CONFIG[$s]["OUT"])) ? $CONFIG[$s]["OUT"] : 0 ;
	
			if ($clear) {$NEWCONFIG[$s]["START_TIME"]["HOUR"]=0;$NEWCONFIG[$s]["START_TIME"]["MINUTES"]=0;}
			else 
				{
				$NEWCONFIG[$s]["START_TIME"]["HOUR"]=(isset($CONFIG[$s]["START_TIME"]["HOUR"])) ? $CONFIG[$s]["START_TIME"]["HOUR"] : 0 ;
				$NEWCONFIG[$s]["START_TIME"]["MINUTES"]=(isset($CONFIG[$s]["START_TIME"]["MINUTES"])) ? $CONFIG[$s]["START_TIME"]["MINUTES"] : 0 ;
				}
			
			if ($clear) $NEWCONFIG[$s]["MINUTES"]=1;
			else $NEWCONFIG[$s]["MINUTES"]=(isset($CONFIG[$s]["MINUTES"])) ? $CONFIG[$s]["MINUTES"] : 1 ;
			
			for ($d=1;$d<=7;$d++) 
				if ($clear) $NEWCONFIG[$s]["DAYS"][$d]=0;
				else $NEWCONFIG[$s]["DAYS"][$d]=(isset($CONFIG[$s]["DAYS"][$d])) ? $CONFIG[$s]["DAYS"][$d] : 0 ;
			
			for ($se=1;$se<=4;$se++) 
				if ($clear) $NEWCONFIG[$s]["SEASONS"][$se]=0;
				else $NEWCONFIG[$s]["SEASONS"][$se]=(isset($CONFIG[$s]["SEASONS"][$se])) ? $CONFIG[$s]["SEASONS"][$se] : 0 ;				
			}
			
		$mqtt->publish($topic, json_encode($NEWCONFIG), 1,true);
		echo "NEW CONFIG PUBLISHED\n";
		$mqtt->close();
		}
	else {echo "TIME OUT CONNECTING!\n";}


 ?>
