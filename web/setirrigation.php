<?php
	require("phpMQTT.php");
	include_once("lib.php");

	// Connect to server
	$client_id = "irrgation-addon-setirrigation";
	$mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);
		
	// check if basic parameters are set
	if (!isset($Parameters["schedule"])) die("KO: NO SCHEDULE.");
	//if (!isset($Parameters["parameter"])) die("KO: NO PARAMETER.");
	//if (!isset($Parameters["value"])) die("KO: NO VALID VALUE.");
	
	// check valid schedule
	$schedule=intval($Parameters["schedule"]);
	if ($schedule<0 || $schedule>($NumSchedules-1)) die("KO: NO VALID SCHEDULE.");
	
	// check valid parameter
	$parameter=$Parameters["parameter"];
	//if($parameter!="OUT" && $parameter!="START_TIME" && $parameter!="MINUTES" && $parameter!="DAYS" && $parameter!="SEASONS") die("KO: NO VALID PARAMETER.");
		
	// date time variant
	$CONFIG=array();
	$NEWCONFIG=array();

	// connect to mqtt server
	if ($mqtt->connect(true, NULL, $username, $password))
		{
		// Subscribe to topic
		$mqtt->subscribe([$topic => ['qos' => $qos,'function' => '__direct_return_message__']]);

		//Store datetime for timeout
		$DATETIME=strtotime("now");
				
		// retrieve config
		do
			{
			// Check new message from mqtt
			$message = $mqtt->proc();
			
			// if new message
			if ($message !== true) $CONFIG=json_decode($message,true);
			}while(empty($CONFIG) && (strtotime("now")-$DATETIME)<10);
			
		// No config retrieved
		if (empty($CONFIG)) die("KO: NO CONFIG RECOVERED\n");
		
		// check json and set the parameter
		if ($Parameters["parameter"]=="JSON")
			{
			if (!isset($Parameters["json"])) die("KO: NO JSON DEFINED.");
			
			// decode parameter json
			$JSON=json_decode($Parameters["json"],true);
			
			if (isset($JSON["OUT"])) $CONFIG[$schedule]["OUT"]=$JSON["OUT"];
			if (isset($JSON["START_TIME"]["HOUR"])) $CONFIG[$schedule]["START_TIME"]["HOUR"]=$JSON["START_TIME"]["HOUR"];
			if (isset($JSON["START_TIME"]["MINUTES"])) $CONFIG[$schedule]["START_TIME"]["HOUR"]=$JSON["START_TIME"]["MINUTES"];
			if (isset($JSON["MINUTES"])) $CONFIG[$schedule]["MINUTES"]=$JSON["MINUTES"];
			for ($d=1;$d<=7;$d++) if (isset($JSON["DAYS"][$d])) $CONFIG[$schedule]["DAYS"][$d]=$JSON["DAYS"][$d];
			for ($s=1;$s<=4;$s++) if (isset($JSON["SEASONS"][$s])) $CONFIG[$schedule]["SEASONS"][$s]=$JSON["SEASONS"][$s];
			}
		// check day and set the parameter
		elseif ($Parameters["parameter"]=="DAYS")
			{
			if(!isset($Parameters["day"])) die("KO: NO DAY DEFINED.");
			
			$day=intval($Parameters["day"]);
			$value=intval($Parameters["value"]);
			
			if($day>7 || $day<1) die("KO: BAD DAY.");
			if($value>1 || $value<0) die("KO: BAD VALUE.");
			
			// set the parameter
			$CONFIG[$schedule][$parameter][$day]=$value;
			}
			
		// check the season and set the parameter
		else if ($Parameters["parameter"]=="SEASONS")
			{
			if(!isset($Parameters["season"])) die("KO: NO SEASON DEFINED.");
			
			$season=intval($Parameters["season"]);
			$value=intval($Parameters["value"]);
			
			if($season>4 || $season<1) die("KO: BAD SEASON.");
			if($value>1 || $value<0) die("KO: BAD VALUE.");
			
			// set the parameter
			$CONFIG[$schedule][$parameter][$season]=$value;			
			}
		// set the out parameter
		else if ($Parameters["parameter"]=="OUT")
			{
			$value=intval($Parameters["value"]);
			
			if($value>2 || $value<0) die("KO: BAD VALUE.");
			
			$CONFIG[$schedule][$parameter]=$value;
			}
		// set minutes parameter
		else if ($Parameters["parameter"]=="MINUTES")
			{
			$value=intval($Parameters["value"]);
			
			if($value>10 || $value<1) die("KO: BAD VALUE.");
			
			$CONFIG[$schedule][$parameter]=$value;
			}
		// set start time parameter
		else if ($Parameters["parameter"]=="START_TIME")
			{
			$hour=intval($Parameters["hour"]);
			$minutes=intval($Parameters["minutes"]);
			
			if($hour>23 || $hour<0) die("KO: BAD HOURS.");
			if($minutes>59 || $minutes<0) die("KO: BAD MINUTES.");
			
			$CONFIG[$schedule][$parameter]["HOUR"]=$hour;
			$CONFIG[$schedule][$parameter]["MINUTES"]=$minutes;
			}
		else die("KO: NO VALID PARAMETER.");

			
		// publish config	
		$mqtt->publish($topic, json_encode($CONFIG), 1,true);
		echo "OK: NEW CONFIG PUBLISHED\n";
		$mqtt->close();
		}
	else {echo "KO: TIME OUT CONNECTING!\n";}


 ?>
