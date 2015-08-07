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
	
	if ($floor_plugin->updateFloorToNull($id)){
		echo json_encode(array('success'=>_('You have successfully deleted this floor\'s background image')));
	} else {
		echo json_encode(array('error'=>_('An error occured.')));
	}