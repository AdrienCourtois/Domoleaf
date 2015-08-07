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
	$data_x = $_POST['x'];
	$data_y = $_POST['y'];
	$test = $floor_plugin->testRightRoomDevice($id);
	
	if (isset($test['error'])){ echo json_encode($test['error']); exit; }
	
	if (!preg_match("#[0-9]{1,3}\/[0-9]{1,3}#", $data_x) || !preg_match("#[0-9]{1,3}\/[0-9]{1,3}#", $data_y)){
		echo json_encode(array('error' => _('The given position isn\'t correct.'))); exit;
	}
	
	if ($floor_plugin->updateRoomDevicePosition($id, $data_x, $data_y)){
		echo json_encode(array('success'=>_('The position have been successfully updated.'))); exit;
	} else {
		echo json_encode(array('error'=>_('An error occured.'))); exit;
	}