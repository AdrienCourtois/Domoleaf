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
	
	$floor_name = $floor_plugin->getFloorName($id);
	$floor_background = $floor_plugin->getFloorBackground($id);
	
	echo _('Update '.$floor_name.'\'s background');
	echo '|||';
	
	if (empty($floor_background)){
		echo _('You don\'t have any background image for this floor.');
		echo '|||';
		echo _('Load one');
	} else {
		echo _('This floor already has a background image:');
		echo '|||';
		echo $floor_background;
		echo '|||';
		echo _('Load another one');
		echo '|||';
		echo _('Delete it');
	}
	
	echo '|||';
	echo _('Close');