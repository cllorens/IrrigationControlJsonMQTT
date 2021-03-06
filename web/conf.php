<?php


# ADDON 

//$json_folder = "/share/jsonmqttscheduler/";
$json_folder = "/share/simplescheduler/";
$SUPERVISOR_TOKEN = getenv('SUPERVISOR_TOKEN');
$HASSIO_URL = "http://hassio/homeassistant/api";
$options_json_file = "/data/options.json";
$sun_filename = $json_folder."sun.dat";

// GET OPTIONS
$options = json_decode(file_get_contents($options_json_file) );

// Merge GET and POST and convert to lower case
$Parameters = array_merge(array_change_key_case($_GET, CASE_LOWER),array_change_key_case($_POST, CASE_LOWER));

// MQTT SERVER
$server 	= $options->MQTT_Server->host;    
$port 		= $options->MQTT_Server->port;
$username 	= $options->MQTT_Server->user;
$password 	= $options->MQTT_Server->pass;
$topic		= $options->MQTT_Server->topic;

$NumSchedules=$options->config->NumSchedules;
$clear= $options->config->clear;
		
$sensors_friendly_name = Array(
	"Vasija",
	"Vasija Litros",
	"Bomba 1",
	"Bomba 2"
	);