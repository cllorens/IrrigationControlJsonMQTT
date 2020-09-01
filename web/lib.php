<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once("conf.php");


//date_default_timezone_set(get_ha_timezone());

//$logfile="scheduler.log";
$logfile="/share/simplescheduler/scheduler.log";

$sun_file_age = 3600 * 6;

	 
if ($options->debug) {
		error_reporting(E_ALL);
    } else {
		error_reporting(0);
	} 

	
$switch_friendly_name=array();


function get_sensor_list() {
	global $switch_friendly_name;
	global $SUPERVISOR_TOKEN;
	global $HASSIO_URL;
	
	$full_sensor_list = array();
	$curlSES=curl_init(); 
	curl_setopt($curlSES,CURLOPT_URL,"$HASSIO_URL/states");
	curl_setopt($curlSES,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curlSES, CURLOPT_HTTPHEADER, array(
		'content-type: application/json',
		"Authorization: Bearer $SUPERVISOR_TOKEN"
	));
	
	$result = json_decode( curl_exec($curlSES));
	curl_close($curlSES);

	if (is_array($result)) save_sunset_sunrise($result);
	
	$c="sensor";
	$sensor_list = array();

	foreach ($result as $item) :
		$eid = $item->entity_id;
		if ( substr( $eid, 0, strlen($c) ) === $c ) :
			$sensor = new stdClass();
			$sensor->entity_id = $eid;
			$sensor->friendly_name =  (isset($item->attributes->friendly_name)) ? $item->attributes->friendly_name : "" ;
			$sensor->state = $item->state;
			$sensor_list[] = $sensor;
			$switch_friendly_name[$eid] = (isset($item->attributes->friendly_name)) ? $item->attributes->friendly_name : "" ;
			unset($sensor);
		endif;
	endforeach;

	return $sensor_list;
}


function call_HA (string $eid, string $action ) {
	global $SUPERVISOR_TOKEN;
	global $HASSIO_URL;

    $domain = explode(".",$eid);
    $command_url = $HASSIO_URL . "/services/" . $domain[0] . "/turn_" . $action;
	
    $postdata = "{\"entity_id\":\"$eid\"}" ;
	
	$curlSES=curl_init(); 
	curl_setopt($curlSES,CURLOPT_URL,"$command_url");
	curl_setopt($curlSES,CURLOPT_POST, 1);
	// curl_setopt($curlSES,CURLOPT_VERBOSE, 1);
	curl_setopt($curlSES,CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($curlSES,CURLOPT_POSTFIELDS, $postdata);
	curl_setopt($curlSES,CURLOPT_HTTPHEADER, array(
		'content-type: application/json',
		"Authorization: Bearer $SUPERVISOR_TOKEN"
	));
	
	$result = curl_exec($curlSES);
	curl_close($curlSES);	
	$ts = date("Y-m-d H:i");
	echo "\n\n$ts --> Turning $action $eid \n\n";
	return $result;
}

function get_ha_timezone() {
	global $SUPERVISOR_TOKEN;
	global $HASSIO_URL;
	
	$curlSES=curl_init(); 
	curl_setopt($curlSES,CURLOPT_URL,"$HASSIO_URL/config");
	curl_setopt($curlSES,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curlSES, CURLOPT_HTTPHEADER, array(
		'content-type: application/json',
		"Authorization: Bearer $SUPERVISOR_TOKEN"
	));
	
	$result = json_decode( curl_exec($curlSES) );
	curl_close($curlSES);
	return $result->time_zone;
}

function load_data() {
	global $json_folder;
	$sched = array();
	$filter = "*.json";
	$filelist = glob($json_folder.$filter);
	foreach ($filelist as $f) {
		$content = file_get_contents($f);
		$sched[] = json_decode($content);
	}
	return $sched;
	
}

function delete_file(string $id) {
	global $json_folder;
	$filename = $json_folder.$id.".json";	
	unlink ($filename);
	return $filename;
}

function create_file(string $eid) {
	global $json_folder;
	$on_dow="";
	$off_dow="";
	$id = ($eid!="") ? $eid : uniqid();
	$filename = $json_folder.$id.".json";
	if ($_POST['on_dow']) foreach($_POST['on_dow'] as $v) $on_dow.= $v;
	if ($_POST['off_dow']) foreach($_POST['off_dow'] as $v) $off_dow.= $v;
	
	$item = new stdClass();	
	$item->id = $id;
	$item->enabled   = $_POST["enabled"];
	$item->entity_id = $_POST["entity_id"];
	$item->on_tod 	 = $_POST["on_tod"];
	$item->on_dow    = $on_dow;
	$item->off_tod 	 = $_POST["off_tod"];
	$item->off_dow 	 = $off_dow;
	file_put_contents($filename, json_encode($item));
	
	return $filename;
}

function get_friendly_html_dow(string $s,bool $on) {
	global $weekdays;
	$html = "<div>";
	$onOffClass = ($on) ? "dowHiglightG" : "dowHiglightR";
	for($i=1; $i<=7; $i++) {
		$d = substr($weekdays[$i],0,2);
		$class = (strpos($s, chr($i+48)  ) !== false ) ? $onOffClass : "" ;
		$html .= "<div class=\"dowIcon $class \" >$d</div> ";
	}
	$html .= "</div>";
	return $html;
	
}

function get_switch_html_select_options() {	
	$html = "";
	$switch_list = get_switch_list();	
	$comp="";
	foreach ($switch_list as $s) {
		$c = explode('.',$s->entity_id); 
		if ($comp!=$c[0]) {
			if ($comp!="") $html .= '</optgroup>';
			$comp=$c[0];
			$html .= '<optgroup label="' . $comp . '">';
		}
		$name = ($s->friendly_name=="") ? $s->entity_id : "$s->friendly_name ($s->entity_id)" ;
		$html .= "<option value=\"$s->entity_id\">$name</option>";
	}
	$html .= '</optgroup>';
	return $html;
}

function is_checked(string $s , int $i) {
	$checked = "checked";
	if (strpos($s, chr($i+48)  ) === false ) $checked = "";
	return $checked;
}

function get_sunset_sunrise() {
	global $sun_filename;
	global $sun_file_age;
	$result = array("sunset"=>"","sunrise"=>"",);
	
	if (!file_exists($sun_filename))  get_switch_list();
	if ( (time()-filemtime($sun_filename) ) > $sun_file_age ) get_switch_list();

	$json=json_decode(file_get_contents($sun_filename));
	$sunset_timestamp = strtotime($json->next_setting);
	$sunrise_timestamp = strtotime($json->next_rising);
	$result["sunset"] = date("H:i",$sunset_timestamp);
	$result["sunrise"] = date("H:i",$sunrise_timestamp);
	
	return $result;
 }
 
 function save_sunset_sunrise(array $json) {
	global $sun_filename;
	global $sun_file_age;
	foreach ($json as $item) : 
	if ($item->entity_id == "sun.sun") :
		$attr = $item->attributes;
		break;
	endif;
	endforeach;
	
	try {	

		if (file_exists($sun_filename)) {
			if ( (time()-filemtime($sun_filename) ) > $sun_file_age ) file_put_contents( $sun_filename,json_encode($attr) );
		} else {
			file_put_contents( $sun_filename,json_encode($attr) );
		}
	} catch (Exception $e) {
		if ($options->debug) echo 'Caught exception: ',  $e->getMessage(), "\n";
	}	

 }
 
 function is_scheduler_running() {
	$response=0;
	try {
		exec("pgrep -af scheduler", $pids);
		foreach ($pids as $p) {		if (strpos($p,"scheduler.php")!== false) $response=1; }
	} catch (Exception $e) {
		if ($options->debug) echo 'Caught exception: ',  $e->getMessage(), "\n";
	}		
	return $response ;
 }
 
 /***************************************************************************/
// FUNCION QUE COMPARA DOS TABLAS ANIDADAS Y DEVUELVE LA DIFERENCIA
/***************************************************************************/
function array_diff_assoc_recursive($array1, $array2)
        {
        //if ($array1=NULL || $array2==NULL) return NULL;
		$difference=array();
		foreach($array1 as $key => $value)
					{
			if( is_array($value) )
							{
							if( !isset($array2[$key]) || !is_array($array2[$key]) )
									{
									$difference[$key] = $value;
									}
							else
									{
									$new_diff = array_diff_assoc_recursive($value, $array2[$key]);
									if(!empty($new_diff)) $difference[$key] = $new_diff;
									}
							}
					else if( !array_key_exists($key,$array2) || $array2[$key] !== $value )
							{
							$difference[$key] = $value;
							}
					}
		return $difference;
        }