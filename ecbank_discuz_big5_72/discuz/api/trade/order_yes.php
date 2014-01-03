<?php
define('IN_DISCUZ', true);
include_once '../../config.inc.php';
require_once '../../include/global.func.php';
require_once '../../include/db_'.$database.'.class.php';
$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true, $dbcharset);
$db->select_db($dbname);
$url = isset($ok_url) ? $ok_url : $return_url;
$arry = parse_url($url);
$query = explode ("&" , $arry['query']);
$p = array();
$v = array();
foreach($query as $value){
	$q = explode ("=" , $value);
	array_push($p,$q[0]);
	array_push($v,"'".$q[1]."'");
}
$sql_field = implode (", ", $p).", admin, status, confirmdate";
$val_field = implode (", ", $v).", 'admin', 1, 0";
$db->query("INSERT INTO {$tablepre}orders ($sql_field) VALUES ($val_field)");
?>