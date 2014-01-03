<?php
$url = isset($_POST['ok_url']) ? $_POST['ok_url'] : $_POST['return_url'];
$arry = parse_url($url);
$query = explode ("&" , $arry['query']);
$p = array();
$v = array();
foreach($query as $value){
	$q = explode ("=" , $value);
	array_push($p,$q[0]);
	array_push($v,$q[1]);
}
DB::insert('forum_order', array(
		$p[0] => $v[0],
		$p[1] => $v[1],
		$p[2] => $v[2],
		$p[3] => $v[3],
		$p[4] => $v[4],
		$p[5] => $v[5],
		$p[6] => $v[6],
		$p[7] => $v[7],
		'status' => "1",
		'admin' => "admin",
		'confirmdate' => "0"
	), false, true);
?>