<?php
	if ($_SERVER['REQUEST_METHOD'] != 'POST'){ exit; }
	
	include('../../../config.php');
	include('../../../functions.php');
	include('../../../libs/Link.class.php');
	
	include('../../../libs/Guest.class.php');
	include('../../../libs/User.class.php');
	include('../../../libs/Admin.class.php');
	include('../../../libs/Root.class.php');
	include('../../../libs/Api.class.php');
	include('../../../libs/FloorPlugin.class.php');
	
	$floor_plugin = new FloorPlugin;
	$id = $_POST['id'];
	$test = $floor_plugin->testIfRightEditFloor($id);
	
	if (isset($test['error'])){ echo json_encode($test['error']); exit; }
	
	$this_floor_background = $floor_plugin->getFloorBackground($id);
	
	if (!empty($this_floor_background) && file_exists('/'.$this_floor_background)){
		unlink('/'.$this_floor_background);
	}
	
	$allowed = array('png', 'jpg', 'gif','zip');
	$target_dir = '../img/user_upload/';
	$dir = 'templates/'.TEMPLATE.'/img/user_upload/';
	
	$this_file = $_FILES[0];
	
	$extension = pathinfo($this_file['name'], PATHINFO_EXTENSION);

	if(!in_array(strtolower($extension), $allowed)){
		echo json_encode(array('error'=>_('The sent file does not exist')));
		exit;
	}
	
	$key = '';
	$keys = array_merge(range(0, 9), range('a', 'z'));
	
	for ($i = 0; $i < 40; $i++) {
		$key .= $keys[array_rand($keys)];
	}
	
	$new_name = $key.'.'.$extension;
	move_uploaded_file($this_file['tmp_name'], $target_dir.$new_name);
	
	if ($floor_plugin->updateFloorBackground($id, $dir.$new_name)){
		echo json_encode(array('success'=>_('Your upload have been successful.')));
		exit;
	} else {
		echo json_encode(array('error'=>_('An error occured')));
		exit;
	}