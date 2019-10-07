<?php

$dbaddress = 'localhost';
$dbuser = 'USERNAME';
$dbpass = 'PASSWORD';
$dbname = 'dragonquestmonsters';

// dbsettings.php overwrites the four $db variables, forces SSL, and turns off error reporting
// ...and possibly overwrites other things from above. I don't know. Do what you want in there.
require_once('config/dbsettings.php');

$db = mysqli_connect($dbaddress,$dbuser,$dbpass);
mysqli_select_db($db,$dbname);
$dbreturn = '';
//echo 'Database initialized.<br>';

//Sends a query to the database.  Return value is stored in dbreturn.
function execute($query){
	global $dbreturn;
	global $db;
	$dbreturn = mysqli_query($db,$query);
	if(!$dbreturn){
		error_log("Error with query: ".$query."\n<br>".mysqli_error($db).'\n<br>');
		echo mysqli_error($db);
	}
}
//Translate dbreturn into a readable PHP array
function get(){
	global $dbreturn;
	global $db;
	$return = array();
	if($dbreturn !== FALSE){
		$array=mysqli_fetch_array($dbreturn);
		if(is_array($array)){
			foreach($array as $key=>$index){
				if(!is_numeric($key)){
					$return[$key] = $array[$key];
				}
			}
		}
	}
	return $return;
}

?>
