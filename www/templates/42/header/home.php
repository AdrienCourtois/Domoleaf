<?php 

include('libs/FloorPlugin.class.php');

echo '<title>'._('Master Command').'</title>';

$request =  new Api();
$request -> send_request();
$request -> add_request('mcDeviceAll');
$request -> add_request('mcVisible');
$request -> add_request('confApplicationAll');
$result  =  $request -> send_request();

$listAllVisible = $result->mcVisible;
$floorallowed = $listAllVisible->ListFloor;

$icons = array(
			1 => 'fa fa-lightbulb-o',
			2 => 'fi flaticon-heating1',
			3 => 'fa fa-bars',
			4 => 'fa fa-bolt',
			5 => 'fi flaticon-snowflake149',
			6 => 'fa fa-volume-up',
			8 => 'fa fa-tree',
			9 => 'fi flaticon-winds4',
			10 => 'fa fa-fire',
			11 => 'fi flaticon-wind34',
			12 => 'fi flaticon-person206',
			13 => 'fa fa-video-camera',
			14 => 'fi flaticon-sign35',
			15 => 'fa fa-sort-amount-asc rotate--90',
			17 => 'fa fa-tachometer'
	);

	$icons_device = array(
			1  => 'fa fa-question',
			2  => 'fa fa-video-camera',
			3  => 'fa fa-lightbulb-o',
			4  => 'fa fa-lightbulb-o',
			5  => 'fa fa-tachometer',
			6  => 'fa fa-lightbulb-o',
			7  => 'fa fa-question',
			8  => 'fa fa-question',
			9  => 'fa fa-question',
			10 => 'fa fa-bars',
			11 => 'fa fa-bars',
			12 => 'fi flaticon-snowflake149',
			13 => 'fi flaticon-snowflake149',
			14 => 'fa fa-volume-up',
			15 => 'fa fa-volume-up',
			17 => 'fa fa-volume-up',
			18 => 'fa fa-tree',
			19 => 'fi flaticon-winds4',
			20 => 'fa fa-fire lg',
			21 => 'fa fa-question',
			22 => 'fi flaticon-engineering',
			23 => 'fa fa-question',
			24 => 'fa fa-question',
			25 => 'fi flaticon-wind34',
			26 => 'fi flaticon-wind34',
			27 => 'fi flaticon-person206',
			28 => 'fa fa-question',
			29 => 'fa fa-video-camera',
			30 => 'fi flaticon-sign35',
			31 => 'fa fa-sort-amount-asc rotate--90',
			32 => 'fa fa-question',
			33 => 'fa fa-question',
			34 => 'fi flaticon-snowflake149',
			35 => 'fa fa-question',
			36 => 'fa fa-question',
			37 => 'fa fa-question',
			38 => 'fa fa-question',
			39 => 'fa fa-question',
			40 => 'fa fa-question',
			41 => 'fa fa-question',
			42 => 'fa fa-question',
			43 => 'fa fa-question',
			44 => 'fa fa-question',
			45 => 'fa fa-question',
			46 => 'fa fa-question',
			47 => 'fa fa-bolt',
			48 => 'fa fa-question',
			49 => 'flaticon-thermometer2',
			50 => 'fa fa-volume-up',
			51 => 'fa fa-question',
			52 => 'fa fa-sort-amount-asc rotate--90',
			53 => 'fa fa-wifi',
			55 => 'fa fa-question',
			56 => 'fa fa-question',
			57 => 'fa fa-question',
			58 => 'fa fa-question',
			59 => 'fa fa-question',
			60 => 'fa fa-question',
			61 => 'flaticon-measure20',
			62 => 'fa fa-question',
			63 => 'fa fa-question',
			65 => 'fa fa-question',
			66 => 'fa fa-question',
			67 => 'fa fa-question',
			68 => 'fa fa-question',
			69 => 'fa fa-question',
			70 => 'fa fa-question',
			71 => 'fa fa-question',
			72 => 'fa fa-question',
			73 => 'fa fa-question',
			75 => 'fa fa-question',
			76 => 'fa fa-question',
			77 => 'fa fa-question',
			78 => 'fa fa-question',
			79 => 'fa fa-question',
			80 => 'fa fa-question',
			81 => 'fa fa-question'
	);
	
?>