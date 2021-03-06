<?php 

include('header.php');

if (!empty($_GET['ipaddr'])){
	$request =  new Api();
	$request ->add_request('confRoomList');
	$request -> add_request('confDeviceNewIp', array($_GET['name'], $_GET['proto'], 
	                                                 $_GET['room'], $_GET['device'], 
	                                                 $_GET['ipaddr'], $_GET['port'], 
	                                                 $_GET['login'], $_GET['pass']));
	$result  =  $request -> send_request();
	$listroom = $result->confRoomList;
	$room_device_id = $result->confDeviceNewIp; 
	echo $listroom->$_GET['room']->floor.'/'.$_GET['room'].'/'.$room_device_id;
}
else if (!empty($_GET['knxaddr'])){
	$request =  new Api();
	$request ->add_request('confRoomList');
	$request -> add_request('confDeviceNewKnx', array($_GET['name'], $_GET['proto'], 
	                                                  $_GET['room'], $_GET['device'], 
	                                                  $_GET['knxaddr'], $_GET['daemon']));
	$result  =  $request -> send_request();
	$listroom = $result->confRoomList;
	$room_device_id = $result->confDeviceNewKnx;
	echo $listroom->$_GET['room']->floor.'/'.$_GET['room'].'/'.$room_device_id;
}
else if (!empty($_GET['enoceanaddr'])){
	$request =  new Api();
	$request ->add_request('confRoomList');
	$request -> add_request('confDeviceNewEnocean', array($_GET['name'], $_GET['proto'], 
	                                                      $_GET['room'], $_GET['device'], 
	                                                      $_GET['enoceanaddr']));
	$result  =  $request -> send_request();
	$listroom = $result->confRoomList;
	$room_device_id = $result->confDeviceNewEnocean;
	echo $listroom->$_GET['room']->floor.'/'.$_GET['room'].'/'.$room_device_id;
}

?>