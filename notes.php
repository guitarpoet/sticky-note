<?php 
	define('uservice', 1); // Set the uservice flag
	require_once(dirname(__FILE__).'/libs/fb.php');
	require_once(dirname(__FILE__).'/libs/database.php');
	require_once(dirname(__FILE__).'/libs/common.php');
	require_once(dirname(__FILE__).'/libs/database.php');

	$type = get_param('type');
	$mapper = Mapper::get_instance();
	if(isset($type)) {
		switch($type) {
		case 'create':
			$id = $mapper->save_entity('notes', array(
				'author' => get_param('author'),
				'message' => get_param('message'),
				'time' => get_param('time')
			));
			echo json_encode(array('id' => $id));
			break;
		case 'delete':
			$mapper->delete_entity('notes', get_param('id'));
			echo json_encode(array('message' => 'ok'));
			break;
		}
	}
	else {
		header('Content-Type:application/json');
		echo json_encode($mapper->exec('list_notes', array(), get_param('page', 0), get_param('items', 15)));
	}
?>
