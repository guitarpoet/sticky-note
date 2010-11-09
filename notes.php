<?php 
	define('uservice', 1); // Set the uservice flag
	require_once(dirname(__FILE__).'/libs/fb.php');
	require_once(dirname(__FILE__).'/libs/database.php');
	require_once(dirname(__FILE__).'/libs/common.php');
	require_once(dirname(__FILE__).'/libs/database.php');

	$update = get_param('udate');
	if(isset($update)) {
	}
	else {
		$mapper = Mapper::get_instance();
		header('Content-Type:application/json');
		echo json_encode($mapper->exec('list_notes', array(), get_param('page', 0), get_param('items', 15)));
	}
?>
