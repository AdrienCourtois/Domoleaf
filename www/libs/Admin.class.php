<?php 

class Admin extends User {

	/*** Profile ***/
	function profileList() {
		$link = Link::get_link('mastercommand');
		$list = array();
	
		$sql = 'SELECT user_id, username, user_mail, lastname, firstname,
		               gender, phone, language, design, activity, user_level
		        FROM user';
		$req = $link->prepare($sql);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[$do->user_id] = clone $do;
		}
		
		return $list;
	}
	
	function profileInfo($id=0) {
		$link = Link::get_link('mastercommand');
		
		if(empty($id)) {
			$id = $this->getId();
		}
		
		$sql = 'SELECT user_id, username, user_mail, lastname, firstname,
		               gender, phone, language, design, activity, user_level
		        FROM user
		        WHERE user_id= :user_id';
		$req = $link->prepare($sql);
		$req->bindValue(':user_id', $id, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		$do = $req->fetch(PDO::FETCH_OBJ);
	
		return $do;
	}
	
	function profileNew($username, $password) {
		$link = Link::get_link('mastercommand');
	
		$sql = 'SELECT user_id
		        FROM user
		        WHERE username= :username';
		$req = $link->prepare($sql);
		$req->bindValue(':username', $username, PDO::PARAM_STR);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		$do = $req->fetch(PDO::FETCH_OBJ);
	
		if(!empty($do->user_id)) {
			return null;
		}
	
		$sql = 'INSERT INTO user
		        (username)
		        VALUES
		        (:username)';
		$req = $link->prepare($sql);
		$req->bindValue(':username', $username, PDO::PARAM_STR);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
	
		$id = $link->lastInsertId();
		
		if(empty($id)) {
			return null;
		}
		
		$sql = 'UPDATE user
		        SET user_password= :pass
		        WHERE user_id=:user_id';
		$req = $link->prepare($sql);
		$req->bindValue(':pass', hash('sha256', $id.'_'.$password), PDO::PARAM_STR);
		$req->bindValue(':user_id', $id, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
			
		$sql = 'INSERT INTO user_floor
		        (user_id, floor_id)
		        SELECT '.$id.', floor_id
		        FROM floor';
		$req = $link->prepare($sql);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		
		$sql = 'INSERT INTO user_room
		        (user_id, room_id)
		        SELECT '.$id.', room_id
		        FROM room';
		$req = $link->prepare($sql);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		
		$sql = 'INSERT INTO user_device
		        (user_id, room_device_id)
		        SELECT '.$id.', room_device_id
		        FROM room_device';
		$req = $link->prepare($sql);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		
		return $id;
	}
	
	function profileRemove($user_id) {
		$link = Link::get_link('mastercommand');
	
		$sql = 'DELETE FROM user
		        WHERE user_id=:user_id';
		$req = $link->prepare($sql);
		$req->bindValue(':user_id', $user_id, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
	}
	
	function profileRename($lastname, $firstname, $gender, $phone, $language, $user_id=0) {
		$link = Link::get_link('mastercommand');
	
		if(empty($user_id)) {
			$user_id = $this->getId();
		}
		
		$sql = 'SELECT user_id
		        FROM user
		        WHERE user_id= :user_id';
		$req = $link->prepare($sql);
		$req->bindValue(':user_id', $user_id, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		$do = $req->fetch(PDO::FETCH_OBJ);
	
		if(!empty($do->user_id)) {
			$user = new User($do->user_id);
			$user-> profileRename($lastname, $firstname, $gender, $phone, $language);
		}
	}
	
	function profileLevel($id, $level) {
		$link = Link::get_link('mastercommand');
		
		//only 3 lvl for the moment
		if($level != 2 && $level != 3) {
			$level = 1;
		}
		
		$sql = 'UPDATE user
		        SET user_level=:level
		        WHERE user_id=:user_id';
		$req = $link->prepare($sql);
		$req->bindValue(':level',   $level, PDO::PARAM_INT);
		$req->bindValue(':user_id', $id,    PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
	}
	
	function profileUsername($id, $username) {
		$link = Link::get_link('mastercommand');
	
		$sql = 'SELECT user_id
		        FROM user
		        WHERE username= :username';
		$req = $link->prepare($sql);
		$req->bindValue(':username', $username, PDO::PARAM_STR);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		$do = $req->fetch(PDO::FETCH_OBJ);
	
		if(!empty($do->user_id)) {
			return null;
		}
	
		$sql = 'UPDATE user
		        SET username=:username
		        WHERE user_id=:user_id';
		$req = $link->prepare($sql);
		$req->bindValue(':username', $username, PDO::PARAM_STR);
		$req->bindValue(':user_id', $id, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
	}
	
	function passwordRename($last, $new, $id=0) {
		if(empty($id)) {
			parent::passwordRename($last, $new);
		}
		else {
			$link = Link::get_link('mastercommand');
			
			$sql = 'SELECT user_id, user_password
			        FROM user
			        WHERE user_id= :user_id';
			$req = $link->prepare($sql);
			$req->bindValue(':user_id', $id, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			$do = $req->fetch(PDO::FETCH_OBJ);
	
			if(!empty($do->user_id)) {
				$sql = 'UPDATE user
				        SET user_password=:user_password
				        WHERE user_id=:user_id';
				$req = $link->prepare($sql);
				$req->bindValue(':user_password', hash('sha256', $do->user_id.'_'.$new), PDO::PARAM_STR);
				$req->bindValue(':user_id', $do->user_id, PDO::PARAM_INT);
				$req->execute() or die (error_log(serialize($req->errorInfo())));
			}
		}
	}
	
	function confRemote($http, $https, $securemode){
		$link = Link::get_link('mastercommand');
		
		$conf = $this->conf_load();
		
		if ($http != $conf[1]->configuration_value or $https != $conf[2]->configuration_value){
			$data = array();
		
			if ($conf[1]->configuration_value != 0){
				$data[] = Array(
									'action' => 'close',
									'configuration_id' => '1',
									'protocol' => 'TCP'
								  );
			}
			if ($conf[2]->configuration_value != 0){
				$data[] = Array(
						'action' => 'close',
						'configuration_id' => '2',
						'protocol' => 'TCP'
				);
			}
			if (sizeof($data) > 0){
				$socket1 = new Socket();
				$socket1->send('cron_upnp', $data);
			}
			
			$sql = 'UPDATE configuration
		       	 	SET configuration_value= :value
					WHERE configuration_id = 1';
			$req = $link->prepare($sql);
			$req->bindValue(':value', $http, PDO::PARAM_STR);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			
			$sql = 'UPDATE configuration
		        SET configuration_value= :value
				WHERE configuration_id = 2';
			$req = $link->prepare($sql);
			$req->bindValue(':value', $https, PDO::PARAM_STR);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			
			$data = array();
			if ($http != 0){
				$data[] = Array(
						'action' => 'open',
						'configuration_id' => '1',
						'protocol' => 'TCP'
				);
			}
			if ($https != 0){
				$data[] = Array(
						'action' => 'open',
						'configuration_id' => '2',
						'protocol' => 'TCP'
				);
			}
			if (sizeof($data) > 0){
				$socket2 = new Socket();
				$socket2->send('cron_upnp', $data);
			}
		}
		
		$sql = 'UPDATE configuration
			        SET configuration_value= :value
					WHERE configuration_id = 3';
		$req = $link->prepare($sql);
		$req->bindValue(':value', $securemode, PDO::PARAM_STR);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		
	}
	
	/*** Floors ***/
	function confFloorList() {
		$link = Link::get_link('mastercommand');
		$list = array();
	
		$sql = 'SELECT floor_id, floor_name
		        FROM floor
		        ORDER BY floor_name ASC';
		$req = $link->prepare($sql);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[$do->floor_id] = clone $do;
		}
		
		return $list;
	}
	
	function confFloorNew($name) {
		$link = Link::get_link('mastercommand');
		
		$sql = 'INSERT INTO floor
		        (floor_name)
		        VALUES
		        (:name)';
		$req = $link->prepare($sql);
		$req->bindValue(':name', $name, PDO::PARAM_STR);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		
		$newfloorid = $link->lastInsertId();
		
		if(!empty($newfloorid)){
			$sql = 'INSERT INTO user_floor
			        (user_id, floor_id)
			        SELECT user_id, '.$newfloorid.'
			        FROM user';
			$req = $link->prepare($sql);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
		}
		
		return $newfloorid;
	}
	
	function confFloorRename($id, $name) {
		$link = Link::get_link('mastercommand');
		
		if(!empty($name)) {
			$sql = 'UPDATE floor
			        SET floor_name=:name
			        WHERE floor_id=:floor_id';
			$req = $link->prepare($sql);
			$req->bindValue(':name', $name, PDO::PARAM_STR);
			$req->bindValue(':floor_id', $id, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
		}
	}
	
	function confFloorRemove($idfloor) {
		
		$link = Link::get_link('mastercommand');
		
		$sql = 'UPDATE user_floor
		        JOIN floor ON user_floor.floor_id = floor.floor_id
		        JOIN user_floor as uf ON uf.floor_id =:floor_id AND user_floor.user_id= uf.user_id
		        SET user_floor.floor_order = user_floor.floor_order - 1  
		        WHERE user_floor.floor_order > uf.floor_order';
		$req = $link->prepare($sql);
		$req->bindValue(':floor_id', $idfloor, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		
		$sql = 'DELETE FROM floor
		        WHERE floor_id=:floor_id';
		$req = $link->prepare($sql);
		$req->bindValue(':floor_id', $idfloor, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
	}
	
	/*** Rooms ***/
	function confRoomAll(){
		$list = array();
		$link = Link::get_link('mastercommand');
		
		$sql = 'SELECT room_id, room_name, floor as id_floor
				FROM room
		        ORDER BY room_name ASC';
		$req = $link->prepare($sql);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[$do->room_id] = clone $do;
		}
		
		return $list;
	}
	
	function confRoomList($floor=0) {
		$list = array();
		$link = Link::get_link('mastercommand');
		
		if(!empty($floor)){
			$sql = 'SELECT room.room_id, room_name, floor, floor_name
			        FROM room
			        JOIN floor ON floor.floor_id=floor
			        WHERE floor_id=:floor_id
			        ORDER BY floor, room_name';
			$req = $link->prepare($sql);
			$req->bindValue(':floor_id', $floor, PDO::PARAM_INT);
		}
		else {
			$sql = 'SELECT room.room_id, room_name, floor, floor_name
			        FROM room
			        JOIN floor ON floor.floor_id=floor
			        ORDER BY floor, room_name';
			$req = $link->prepare($sql);
		}
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[$do->room_id] = clone $do;
		}
	
		return $list;
	}
	
	function confRoomNew($name, $floor) {
		$link = Link::get_link('mastercommand');
		$floorList = $this->confFloorList();
	
		if(empty($name) or empty($floorList[$floor])) {
			return null;
		}
	
		$sql = 'INSERT INTO room
		        (room_name, floor)
		        VALUES
		        (:name, :floor)';
		$req = $link->prepare($sql);
		$req->bindValue(':name',  $name,  PDO::PARAM_STR);
		$req->bindValue(':floor', $floor, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		
		$newroomid = $link->lastInsertId();
		
		if(!empty($newroomid)){
			$sql = 'INSERT INTO user_room
			        (user_id, room_id)
			        SELECT user_id, '.$newroomid.'
			        FROM user';
			$req = $link->prepare($sql);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
		}
	}
	
	function confRoomRename($id, $name) {
		$link = Link::get_link('mastercommand');
	
		$sql = 'SELECT room_id, room_name
		        FROM room
		        WHERE room_id=:room_id';
		$req = $link->prepare($sql);
		$req->bindValue(':room_id', $id, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		$do = $req->fetch(PDO::FETCH_OBJ);
	
		if(!empty($do->room_id)) {
			$sql = 'UPDATE room
			        SET room_name=:name
			        WHERE room_id=:room_id';
			$req = $link->prepare($sql);
			$req->bindValue(':room_id', $do->room_id, PDO::PARAM_INT);
			$req->bindValue(':name',    $name,        PDO::PARAM_STR);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
		}
	}
	
	function confRoomFloor($id, $floor) {
		$link = Link::get_link('mastercommand');
		$floorList = $this->confFloorList();
		
		$sql = 'SELECT room_id, room_name
		        FROM room
		        WHERE room_id=:room_id';
		$req = $link->prepare($sql);
		$req->bindValue(':room_id', $id, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		$do = $req->fetch(PDO::FETCH_OBJ);
	
		if(!empty($do->room_id) && !empty($floorList[$floor])) {
			$sql = 'UPDATE room
			        SET floor=:floor
			        WHERE room_id=:room_id';
			$req = $link->prepare($sql);
			$req->bindValue(':room_id', $do->room_id, PDO::PARAM_INT);
			$req->bindValue(':floor',   $floor,       PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
		}
	}
	
	function confRoomRemove($idroom, $idfloor) {
		$link = Link::get_link('mastercommand');
	
		$sql = 'UPDATE user_room
		        JOIN room ON user_room.room_id = room.room_id
		        JOIN user_room as ur ON ur.room_id =:room_id AND user_room.user_id= ur.user_id
		        SET user_room.room_order = user_room.room_order - 1
		        WHERE room.floor=:floor_id AND user_room.room_order > ur.room_order';
		
		$req = $link->prepare($sql);
		$req->bindValue(':floor_id', $idfloor, PDO::PARAM_INT);
		$req->bindValue(':room_id', $idroom, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		
		$sql = 'DELETE FROM room
		        WHERE room_id=:room_id';
		$req = $link->prepare($sql);
		$req->bindValue(':room_id', $idroom, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
	}
	
	/*** Devices ***/
	
	function confRoomDeviceAll($iddevice){
		$list = array();
		$link = Link::get_link('mastercommand');
		
		$sql = 'SELECT room_device_id, name
		        FROM room_device
		        WHERE room_device_id=:room_device_id';
		$req = $link->prepare($sql);
		$req->bindValue(':room_device_id', $iddevice, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[$do->room_device_id] = array(
				'room_device_id' => $do->room_device_id,
				'name'           => $do->name
			);
		}
		return $list;
	}
	
	function confRoomDeviceRemove($iddevice, $idroom){
		$link = Link::get_link('mastercommand');
		
		$sql = 'UPDATE user_device
		        JOIN room_device ON user_device.room_device_id = room_device.room_device_id
		        JOIN user_device as ud ON ud.room_device_id =:room_device_id AND 
		                                  user_device.user_id= ud.user_id
		        SET user_device.device_order = user_device.device_order - 1
		        WHERE room_device.room_id=:room_id AND 
		              user_device.device_order > ud.device_order';
		$req = $link->prepare($sql);
		$req->bindValue(':room_device_id', $iddevice, PDO::PARAM_INT);
		$req->bindValue(':room_id', $idroom, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		
		$sql = 'DELETE FROM room_device
		        WHERE room_device_id=:room_device_id';
		$req = $link->prepare($sql);
		$req->bindValue(':room_device_id', $iddevice, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
	}
	
	/**
	 * Save all device information
	 * @param int: room id
	 * @param string : device name
	 * @param int : daemon id
	 * @param string : device address
	 * @param int : device id
	 * @param int : device port
	 * @param string : login
	 * @param string : password
	 * @return NULL
	 */
	function confDeviceSaveInfo($idroom, $name, $daemon=0, $devaddr, $iddevice, $port='', $login='', $pass=''){
		$link = Link::get_link('mastercommand');
		
		if(empty($idroom) or empty($name) or empty($devaddr) or empty($iddevice)) {
			return null;
		}
		
		if(empty($daemon) or $daemon == 'undefined'){
			$daemon = null;
		}
		
		$sql = 'SELECT room_id
		        FROM room_device
		        WHERE room_device.room_device_id=:room_device_id';
		$req = $link->prepare($sql);
		$req->bindValue(':room_device_id', $iddevice, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		$do = $req->fetch(PDO::FETCH_OBJ);
		
		if($do->room_id != $idroom){
			$sql = 'UPDATE user_device
			        JOIN room_device ON user_device.room_device_id = room_device.room_device_id
			        JOIN user_device as ud ON ud.room_device_id =:room_device_id AND 
			                                  user_device.user_id= ud.user_id
			        SET user_device.device_order = user_device.device_order - 1
			        WHERE room_device.room_id=:room_id AND 
			              user_device.device_order > ud.device_order AND 
			              user_device.device_order > 1';
			$req = $link->prepare($sql);
			$req->bindValue(':room_device_id', $iddevice, PDO::PARAM_INT);
			$req->bindValue(':room_id', $do->room_id, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			
			$sql = 'UPDATE user_device
			        SET device_order = 0, device_allowed = 0
			        WHERE room_device_id=:room_device_id';
			$req = $link->prepare($sql);
			$req->bindValue(':room_device_id', $iddevice, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
		}
		
		if(empty($pass)){
			$sql = 'UPDATE room_device
			        SET name=:name, daemon_id=:daemon_id, addr=:addr, 
			            room_id=:room_id, plus1=:plus1, plus2=:plus2
			        WHERE room_device_id=:room_device_id';
			$req = $link->prepare($sql);
			$req->bindValue(':name',  ucfirst($name),  PDO::PARAM_STR);
			$req->bindValue(':daemon_id', $daemon, PDO::PARAM_INT);
			$req->bindValue(':addr', $devaddr, PDO::PARAM_STR);
			$req->bindValue(':room_id', $idroom, PDO::PARAM_INT);
			$req->bindValue(':room_device_id', $iddevice, PDO::PARAM_INT);
			$req->bindValue(':plus1', $port, PDO::PARAM_STR);
			$req->bindValue(':plus2', $login, PDO::PARAM_STR);
		}
		else {
			$sql = 'UPDATE room_device
			        SET name=:name, daemon_id=:daemon_id, addr=:addr, 
			            room_id=:room_id, plus1=:plus1, plus2=:plus2, 
			            plus3=:plus3 
			        WHERE room_device_id=:room_device_id';
			$req = $link->prepare($sql);
			$req->bindValue(':name',  ucfirst($name),  PDO::PARAM_STR);
			$req->bindValue(':daemon_id', $daemon, PDO::PARAM_INT);
			$req->bindValue(':addr', $devaddr, PDO::PARAM_STR);
			$req->bindValue(':room_id', $idroom, PDO::PARAM_INT);
			$req->bindValue(':room_device_id', $iddevice, PDO::PARAM_INT);
			$req->bindValue(':plus1', $port, PDO::PARAM_STR);
			$req->bindValue(':plus2', $login, PDO::PARAM_STR);
			$req->bindValue(':plus3', $pass, PDO::PARAM_STR);
		}
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		
		if(empty($daemon)){
			$socket = new Socket();
			$socket->send('reload_camera');
		}
	}
	
	/**
	 * Save device options
	 * @param int : device id
	 * @param array : option information
	 * @return NULL
	 */
	function confDeviceSaveOption($room_device_id, $options){ 
		$link = Link::get_link('mastercommand');
		
		if(empty($room_device_id) or empty($options)){
			return null;
		}
		
		$sql = 'SELECT room_device_id
		        FROM room_device_option
		        WHERE room_device_id=:room_device_id AND option_id=:option_id';
		$req = $link->prepare($sql);
		$req->bindValue(':room_device_id', $room_device_id, PDO::PARAM_INT);
		$req->bindValue(':option_id',  $options['id'],  PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		$do = $req->fetch(PDO::FETCH_OBJ);
		
		if(empty($options['status']) or $options['status'] == 'false'){
			$status = 0;
		}
		else {
			$status = 1;
		}
		
		if(!empty($do->room_device_id)) {
			$sql = 'UPDATE room_device_option
			        SET option_id=:option_id, addr=:addr, addr_plus=:addr_plus, 
			            status=:status
			        WHERE room_device_id=:room_device_id AND 
			              option_id=:option_id';
			$req = $link->prepare($sql);
			$req->bindValue(':room_device_id', $room_device_id, PDO::PARAM_INT);
			$req->bindValue(':option_id', $options['id'], PDO::PARAM_INT);
			$req->bindValue(':addr', $options['addr'], PDO::PARAM_STR);
			$req->bindValue(':addr_plus', $options['addr_plus'], PDO::PARAM_STR);
			$req->bindValue(':status', $status, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
		}
		else {
			$sql = 'INSERT INTO room_device_option
			        (room_device_id, option_id, addr, addr_plus, status)
			        VALUES
			        (:room_device_id, :option_id, :addr, :addr_plus, :status)';
			$req = $link->prepare($sql);
			$req->bindValue(':room_device_id', $room_device_id, PDO::PARAM_INT);
			$req->bindValue(':option_id', $options['id'], PDO::PARAM_INT);
			$req->bindValue(':addr', $options['addr'], PDO::PARAM_STR);
			$req->bindValue(':addr_plus', $options['addr_plus'], PDO::PARAM_STR);
			$req->bindValue(':status', $status, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
		}
	}
	
	/**
	 * Return all options from a device
	 * @param id : device id
	 * @return all options
	 */
	
	function confDeviceRoomOpt($deviceroomid) { 
		$link = Link::get_link('mastercommand');
		$list = array();
		
		$sql = 'SELECT option_id, addr, addr_plus, status, valeur
		        FROM  room_device_option
		        WHERE room_device_id=:room_device_id';
		$req = $link->prepare($sql);
		$req->bindValue(':room_device_id',  $deviceroomid,  PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[$do->option_id] = clone $do;
		}
		
		return $list;
	}
	
	function confRoomDeviceList($room){
		$link = Link::get_link('mastercommand');
		$list = array();
		
		if(!empty($room)){
			$sql = 'SELECT room_device_id, room_device.name, 
			               room_device.protocol_id, room_id, 
			               if(device.name'.$this->getLanguage().' = "", device.name, device.name'.$this->getLanguage().') as device_name, 
			               room_device.device_id, daemon_id, addr, device.application_id
			        FROM room_device
			        JOIN device ON room_device.device_id = device.device_id
			        WHERE room_id=:room_id
			        ORDER BY name ASC';
			$req = $link->prepare($sql);
			$req->bindValue(':room_id', $room, PDO::PARAM_INT);
		}
		else {
			$sql = 'SELECT room_device_id, room_device.name, 
			               room_device.protocol_id, room_id, 
			               if(device.name'.$this->getLanguage().' = "", device.name, device.name'.$this->getLanguage().') as device_name, 
			               room_device.device_id, daemon_id, addr, plus1, plus2, device.application_id
			        FROM room_device
			        JOIN device ON room_device.device_id = device.device_id
			        ORDER BY name ASC';
			$req = $link->prepare($sql);
		}
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[$do->room_device_id] = clone $do;
		}
		
		return $list;
	}
	
	function confDeviceProtocol($device=0) {
		$link = Link::get_link('mastercommand');
		$list = array();
		
		$sql = 'SELECT protocol_id
		        FROM device_protocol
		        WHERE device_id=:device_id';
		$req = $link->prepare($sql);
		$req->bindValue(':device_id', $device, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[] = $do->protocol_id;
		}
		
		return $list;
	}
	
	function confDeviceNewIp($name, $proto, $room, $device, $addr, $port='80', $login='', $pass=''){
		$link = Link::get_link('mastercommand');
		
		if(empty($name) or empty($proto) or 
		   empty($room) or empty($device) or empty($addr)) {
			return 0;
		}
		
		if(empty($port) || !is_numeric($port)){
			$port = '80';
		}
		
		if($port < 0 || $port > 65535){
			$port = '80';
		}
		
		$sql = 'INSERT INTO room_device
		        (name, protocol_id, room_id, device_id, addr, plus1, plus2, plus3)
		        VALUES
		        (:name, :proto, :room, :device, :addr, :port, :login, :pass)';
		$req = $link->prepare($sql);
		$req->bindValue(':name',  ucfirst($name),  PDO::PARAM_STR);
		$req->bindValue(':proto', $proto, PDO::PARAM_INT);
		$req->bindValue(':room', $room, PDO::PARAM_INT);
		$req->bindValue(':device', $device, PDO::PARAM_INT);
		$req->bindValue(':addr', $addr, PDO::PARAM_STR);
		$req->bindValue(':port', $port, PDO::PARAM_STR);
		$req->bindValue(':login', $login, PDO::PARAM_STR);
		$req->bindValue(':pass', $pass, PDO::PARAM_STR);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
	
		$newdeviceid = $link->lastInsertId();
		
		if(!empty($newdeviceid)){
			$sql = 'INSERT INTO user_device
			        (user_id, room_device_id)
			        SELECT user_id, '.$newdeviceid.'
			        FROM user';
			$req = $link->prepare($sql);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
		}
		
		return $newdeviceid;
	}
	
	function confDeviceNewKnx($name, $proto, $room, $device, $addr, $daemon){
		$link = Link::get_link('mastercommand');
		
		if(empty($name) or empty($proto) or empty($room) or 
		   empty($device) or empty($addr) or empty($daemon)) {
			return 0;
		}
		
		$sql = 'INSERT INTO room_device
		        (name, protocol_id, room_id, device_id, addr, daemon_id)
		        VALUES
		        (:name, :proto, :room, :device, :addr, :dae)';
		$req = $link->prepare($sql);
		$req->bindValue(':name',  ucfirst($name),  PDO::PARAM_STR);
		$req->bindValue(':proto', $proto, PDO::PARAM_INT);
		$req->bindValue(':room', $room, PDO::PARAM_INT);
		$req->bindValue(':device', $device, PDO::PARAM_INT);
		$req->bindValue(':addr', $addr, PDO::PARAM_STR);
		$req->bindValue(':dae', $daemon, PDO::PARAM_STR);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		
		$newdeviceid = $link->lastInsertId();
		
		if(!empty($newdeviceid)){
			$sql = 'INSERT INTO user_device
			        (user_id, room_device_id)
			        SELECT user_id, '.$newdeviceid.'
			        FROM user';
			$req = $link->prepare($sql);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
		}
		
		return $newdeviceid;
	}
	
	function confDeviceNewEnocean($name, $proto, $room, $device, $addr){
		$link = Link::get_link('mastercommand');
		
		if(empty($name) or empty($proto) or 
		   empty($room) or empty($device) or empty($addr)) {
			return 0;
		}
		
		$sql = 'INSERT INTO room_device
		        (name, protocol_id, room_id, device_id, addr)
		        VALUES
		        (:name, :proto, :room, :device, :addr)';
		$req = $link->prepare($sql);
		$req->bindValue(':name',  ucfirst($name),  PDO::PARAM_STR);
		$req->bindValue(':proto', $proto, PDO::PARAM_INT);
		$req->bindValue(':room', $room, PDO::PARAM_INT);
		$req->bindValue(':device', $device, PDO::PARAM_INT);
		$req->bindValue(':addr', $addr, PDO::PARAM_STR);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		
		$newdeviceid = $link->lastInsertId();
		
		if(!empty($newdeviceid)){
			$sql = 'INSERT INTO user_device
			        (user_id, room_device_id)
			        SELECT user_id, '.$newdeviceid.'
			        FROM user';
			$req = $link->prepare($sql);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
		}
		
		return $newdeviceid;
	}
	
	/*** Daemon management ***/
	function confDaemonList() {
		$link = Link::get_link('mastercommand');
		$list = array();
		
		$sql = 'SELECT daemon_id, name, serial, validation
		        FROM daemon
		        ORDER BY name';
		$req = $link->prepare($sql);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[$do->daemon_id] = array(
				'daemon_id' => $do->daemon_id,
				'name'      => $do->name,
				'serial'    => $do->serial,
				'validation'=> $do->validation,
				'protocol'  => array()
			);
		}
		
		$sql = 'SELECT daemon_id, protocol_id
		        FROM daemon_protocol';
		$req = $link->prepare($sql);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[$do->daemon_id]['protocol'][] = $do->protocol_id;
		}
		
		return $list;
	}
	
	function confDaemonNew($name, $serial, $skey) {
		$link = Link::get_link('mastercommand');
		
		if(empty($name) or empty($serial) or empty($skey)) {
			return 0;
		}
		
		$sql = 'INSERT INTO daemon
		        (name, serial, secretkey)
		        VALUES
		        (:name, :serial, :skey)';
		$req = $link->prepare($sql);
		$req->bindValue(':name', mb_strtoupper($name),  PDO::PARAM_STR);
		$req->bindValue(':serial', mb_strtoupper($serial), PDO::PARAM_STR);
		$req->bindValue(':skey', md5($skey), PDO::PARAM_STR);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		
		return $link->lastInsertId();
	}
	
	function confDaemonRemove($id) {
		$link = Link::get_link('mastercommand');
	
		$sql = 'DELETE FROM daemon
		        WHERE daemon_id=:daemon_id';
		$req = $link->prepare($sql);
		$req->bindValue(':daemon_id', $id, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
	}
	
	function confDaemonRename($id, $name, $serial, $skey='') {
		$link = Link::get_link('mastercommand');
		
		if(!empty($name) && !empty($serial)) {
			$sql = 'SELECT serial
			        FROM daemon
			        WHERE daemon_id=:daemon_id';
			$req = $link->prepare($sql);
			$req->bindValue(':daemon_id', $id, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			$do = $req->fetch(PDO::FETCH_OBJ);
			if(empty($do)){
				return null;
			}
			
			$currentserial = $do->serial;
			
			$sql = 'UPDATE daemon
			        SET name=:name, serial=:serial
			        WHERE daemon_id=:daemon_id';
			$req = $link->prepare($sql);
			$req->bindValue(':name', $name, PDO::PARAM_STR);
			$req->bindValue(':serial', $serial, PDO::PARAM_STR);
			$req->bindValue(':daemon_id', $id, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			if(!empty($skey)){
				$sql = 'UPDATE daemon
				        SET secretkey=:skey, validation=0
				        WHERE daemon_id=:daemon_id';
				$req = $link->prepare($sql);
				$req->bindValue(':skey', md5($skey), PDO::PARAM_STR);
				$req->bindValue(':daemon_id', $id, PDO::PARAM_INT);
				$req->execute() or die (error_log(serialize($req->errorInfo())));
			}
			else if($currentserial != $serial){
				$sql = 'UPDATE daemon
				        SET validation=0
				        WHERE daemon_id=:daemon_id';
				$req = $link->prepare($sql);
				$req->bindValue(':daemon_id', $id, PDO::PARAM_INT);
				$req->execute() or die (error_log(serialize($req->errorInfo())));
			}
		}
	}
	
	function confDaemonProtocolList() {
		$link = Link::get_link('mastercommand');
		$list = array();
		
		$sql = 'SELECT protocol_id, wired,
		               if(name'.$this->getLanguage().' = "", name, name'.$this->getLanguage().') as name
		        FROM protocol
		        WHERE specific_daemon=1
		        ORDER BY name'.$this->getLanguage();
		$req = $link->prepare($sql);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[$do->protocol_id] = clone $do;
		}
		
		return $list;
	}
	
	function confDaemonProtocol($daemon, $newProtocolList=array()) {
		$link = Link::get_link('mastercommand');
		
		$daemonList = $this->confDaemonList();
		$protocolList = $this->confDaemonProtocolList();
		
		if(empty($daemonList) or empty($daemonList[$daemon])) {
			return null;
		}
		
		$sql = 'DELETE FROM daemon_protocol
		        WHERE daemon_id=:daemon_id';
		$req = $link->prepare($sql);
		$req->bindValue(':daemon_id', $daemon, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		
		if(!empty($newProtocolList) && sizeof($newProtocolList) > 0) {
			foreach ($newProtocolList as $protocol) {
				if(!empty($protocolList[$protocol])) {
					$sql = 'INSERT INTO daemon_protocol
					        (daemon_id, protocol_id)
					        VALUES
					        (:daemon_id, :protocol_id)';
					$req = $link->prepare($sql);
					$req->bindValue(':daemon_id',   $daemon,   PDO::PARAM_INT);
					$req->bindValue(':protocol_id', $protocol, PDO::PARAM_INT);
					$req->execute() or die (error_log(serialize($req->errorInfo())));
				}
			}
		}
	}
	
	function confDaemonSendValidation($iddaemon){
		$socket = new Socket();
		$data = array(
			'daemon_id' => $iddaemon
		);
		$socket->send('check_slave', $data, 1);
		
		return $socket->receive();
	}
	
	
	/*** User permission ***/
	function mcAllowed(){
		$link = Link::get_link('mastercommand');
		
		$listFloor = array();
		$listRoom  = array();
		$listDevice= array();
		$listApps  = array();
		
		$sql = 'SELECT floor_name, user_floor.floor_id, user_floor.floor_order
		        FROM user_floor
		        JOIN floor ON user_floor.floor_id=floor.floor_id
		        WHERE user_id=:user_id
		        ORDER BY floor_order ASC';
		$req = $link->prepare($sql);
		$req->bindValue(':user_id', $this->getId(), PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$listFloor[$do->floor_id] = array(
				'floor_name' => $do->floor_name,
				'floor_id'   => $do->floor_id,
				'floor_order'=> $do->floor_order
			);
		}
		
		$sql = 'SELECT room.room_name, room.room_id, user_room.room_order, 
		               floor
		        FROM room
		        JOIN user_room ON room.room_id=user_room.room_id
		        JOIN user_floor ON room.floor=user_floor.floor_id AND
		                           user_floor.user_id=user_room.user_id
		        WHERE user_room.user_id=:user_id
		        ORDER BY user_floor.floor_order ASC, room_order ASC';
		$req = $link->prepare($sql);
		$req->bindValue(':user_id', $this->getId(), PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$listRoom[$do->room_id] = array(
				'room_name' => $do->room_name,
				'room_id'   => $do->room_id,
				'room_order'=> $do->room_order,
				'floor_id'  => $do->floor
			);
		}
		
		$sql = 'SELECT room_device.name, room_device.room_device_id,
		               room_device.room_id, room_order,
		               user_device.device_order, application_id,
		               room_device.device_id, room_device.protocol_id
		        FROM room_device
		        JOIN device ON room_device.device_id=device.device_id
		        JOIN user_device ON room_device.room_device_id=user_device.room_device_id
		        JOIN user_room ON room_device.room_id=user_room.room_id AND 
		                          user_room.user_id=user_device.user_id
		        JOIN room ON room.room_id=room_device.room_id
		        JOIN user_floor ON room.floor=user_floor.floor_id AND
		                          user_floor.user_id=user_device.user_id
		        WHERE user_device.user_id=:user_id
		        ORDER BY user_floor.floor_order ASC, user_room.room_order ASC, 
		                 user_device.device_order ASC';
		$req = $link->prepare($sql);
		$req->bindValue(':user_id', $this->getId(), PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$listDevice[$do->room_device_id] = array(
				'room_id'       => $do->room_id,
				'application_id'=> $do->application_id,
				'device_id'     => $do->device_id,
				'protocol_id'   => $do->protocol_id,
				'name'          => $do->name,
				'room_device_id'=> $do->room_device_id,
				'device_order'  => $do->device_order,
				'device_opt'    => array()
			);
			if(!in_array($do->application_id, $listApps)){
				$listApps[] = $do->application_id;
			}
		}
		
		$sql = 'SELECT room_device.room_device_id, room_device.room_id, 
		               optiondef.hidden_arg, room_device.device_id, 
		               optiondef.option_id,
		               if(optiondef.name'.$this->getLanguage().' = "", optiondef.name, optiondef.name'.$this->getLanguage().') as name,
		               room_device_option.addr, room_device_option.addr_plus, 
		               room_device_option.valeur
		        FROM room_device
		        JOIN room_device_option ON room_device_option.room_device_id = room_device.room_device_id
		        JOIN optiondef ON room_device_option.option_id = optiondef.option_id
		        WHERE room_device_option.status = 1';
		$req = $link->prepare($sql);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while($do = $req->fetch(PDO::FETCH_OBJ)) {
			if($do->hidden_arg & 4 and !empty($listDevice[$do->room_device_id])) {
				$listDevice[$do->room_device_id]['device_opt'][$do->option_id] = array(
					'option_id' => $do->option_id,
					'name'      => $do->name,
					'addr'      => $do->addr,
					'addr_plus' => $do->addr_plus,
					'valeur'    => $do->valeur
				);
			}
		}
		
		return array(
			'ListFloor' => $listFloor,
			'ListRoom'  => $listRoom,
			'ListDevice'=> $listDevice,
			'ListApp'	=> $listApps
		);
	}
	
	function mcVisible(){
		$link = Link::get_link('mastercommand');
		
		$listFloor = array();
		$listRoom = array();
		$listDevice = array();
		$listApps= array();
		
		$listall = $this->mcallowed();
		
		foreach ($listall['ListFloor'] as $elem) {
			if($elem['floor_order'] > 0) {
				$listFloor[$elem['floor_id']] = $elem;
			}
		}
		
		foreach ($listall['ListRoom'] as $elem) {
			if($elem['room_order'] > 0) {
				$listRoom[$elem['room_id']] = $elem;
			}
		}
		
		foreach ($listall['ListDevice'] as $elem) {
			if($elem['device_order'] > 0) {
				$listDevice[$elem['room_device_id']] = $elem;
				if(!in_array($elem['application_id'], $listApps)) {
					$listApps[] = $elem['application_id'];
				}
			}
		}
		
		return array(
				'ListFloor'  => $listFloor,
				'ListRoom'   => $listRoom,
				'ListDevice' => $listDevice,
				'ListApp'    => $listApps
		);
	}
	
	function confUserInstallation($userid) {
		$link = Link::get_link('mastercommand');
		
		if(empty($userid)) {
			$userid = $this->getId();
		}
		
		$list = array();
	
		$sql = 'SELECT floor.floor_id, floor_name, floor_order
		        FROM floor
		        JOIN user_floor ON user_floor.floor_id=floor.floor_id
		        WHERE user_id=:user_id
		        ORDER BY floor_order ASC';
		$req = $link->prepare($sql);
		$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[$do->floor_id] = array(
				'floor_id'     => $do->floor_id,
				'floor_name'   => $do->floor_name,
				'floor_allowed'=> 1,
				'floor_order'  => $do->floor_order,
				'room'         => array()
			);
		}
		
		$sql = 'SELECT room.room_id, room_name, floor, room_order
		        FROM room
		        JOIN user_room ON user_room.room_id = room.room_id
		        WHERE user_id=:user_id
		        ORDER BY room_order ASC';
		$req = $link->prepare($sql);
		$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[$do->floor]['room'][$do->room_id] = array(
				'room_id'     => $do->room_id,
				'room_name'   => $do->room_name,
				'room_allowed'=> 1,
				'room_order'  => $do->room_order,
				'devices'     => array()
			);
		}
		
		$sql = 'SELECT room_device.room_device_id, room_device.name, 
		               room_device.room_id, room.floor, device_order
		        FROM room_device
		        JOIN room ON room_device.room_id = room.room_id
		        JOIN user_device ON user_device.room_device_id=room_device.room_device_id
		        WHERE user_id=:user_id
		        ORDER BY device_order ASC';
		$req = $link->prepare($sql);
		$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[$do->floor]['room'][$do->room_id]['devices'][$do->room_device_id] = array(
				'room_device_id'=> $do->room_device_id,
				'name'          => $do->name,
				'device_order'  => $do->device_order,
				'device_allowed'=> 1
			);
		}
		
		return $list;
	}
	
	//device
	
	function confUserDeviceEnable($userid){
		$link = Link::get_link('mastercommand');
		$list = array();
	
		$sql = 'SELECT user_id, room_device_id, device_allowed, device_order
		        FROM user_device
		        WHERE user_id=:user_id
		        ORDER BY room_device_id ASC';
		$req = $link->prepare($sql);
		$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[$do->room_device_id] = array(
				'user_id'       => $do->user_id,
				'room_device_id'=> $do->room_device_id,
				'device_allowed'=> $do->device_allowed,
				'device_order'  => $do->device_order
			);
		}
		
		return $list;
	}

	function confUserPermissionDevice($userid, $deviceid, $status){
		$link = Link::get_link('mastercommand');
		
		$sql = 'SELECT user_device.room_device_id, device_order, room_id
		        FROM user_device
		        JOIN room_device ON user_device.room_device_id = room_device.room_device_id
		        WHERE user_device.room_device_id=:room_device_id AND 
		              user_device.user_id=:user_id';
		$req = $link->prepare($sql);
		$req->bindValue(':room_device_id', $deviceid, PDO::PARAM_INT);
		$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		$do = $req->fetch(PDO::FETCH_OBJ);
		
		if($status == 1){
			$sql = 'UPDATE user_device
			        SET device_allowed = 1
			        WHERE user_id=:user_id AND 
			              room_device_id=:room_device_id';
			$req = $link->prepare($sql);
			$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
			$req->bindValue(':room_device_id', $deviceid, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
		}
		else {
			$sql = 'UPDATE user_device
			        SET device_allowed = 0
			        WHERE user_id=:user_id AND room_device_id=:room_device_id';
			$req = $link->prepare($sql);
			$req->bindValue(':room_device_id', $deviceid, PDO::PARAM_INT);
			$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
		}
	}
	
	//room
	
	function confUserRoomEnable($userid){
		$link = Link::get_link('mastercommand');
		$list = array();
		
		$sql = 'SELECT user_id, room_id, room_allowed, room_order
		        FROM user_room
		        WHERE user_id=:user_id
		        ORDER BY room_id ASC';
		$req = $link->prepare($sql);
		$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[$do->room_id] = array(
				'user_id'     => $do->user_id,
				'room_id'     => $do->room_id,
				'room_allowed'=> $do->room_allowed,
				'room_order'  => $do->room_order
			);
		}
		
		return $list;
	}
	
	function confUserPermissionRoom($userid, $roomid, $status){
		$link = Link::get_link('mastercommand');
		
		$sql = 'SELECT user_room.room_id, room_order, floor
		        FROM user_room
		        JOIN room ON user_room.room_id = room.room_id
		        WHERE user_room.user_id=:user_id AND 
		              user_room.room_id=:room_id';
		$req = $link->prepare($sql);
		$req->bindValue(':room_id', $roomid, PDO::PARAM_INT);
		$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		$do = $req->fetch(PDO::FETCH_OBJ);
		
		if($status == 1){
			$sql = 'UPDATE user_room
			        SET room_allowed = 1
			        WHERE user_id=:user_id AND room_id=:room_id';
			$req = $link->prepare($sql);
			$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
			$req->bindValue(':room_id', $roomid, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
		}
		else {
			$sql = 'UPDATE user_room
			        SET room_allowed = 0
			        WHERE user_id=:user_id AND room_id=:room_id';
			$req = $link->prepare($sql);
			$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
			$req->bindValue(':room_id', $roomid, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
		}
	}
	
//Floor

	function confUserFloorEnable($userid){
		$link = Link::get_link('mastercommand');
		$list = array();
		
		$sql = 'SELECT user_id, floor_id, floor_allowed, floor_order
		        FROM user_floor
		        WHERE user_id=:user_id
		        ORDER BY floor_id ASC';
		$req = $link->prepare($sql);
		$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[$do->floor_id] = array(
				'user_id'      => $do->user_id,
				'floor_id'     => $do->floor_id,
				'floor_allowed'=> $do->floor_allowed,
				'floor_order'  => $do->floor_order
			);
		}
		return $list;
	}
	
	function confUserPermissionFloor($userid, $floorid, $status){
		$link = Link::get_link('mastercommand');
		
		$sql = 'SELECT floor_id
		        FROM user_floor
		        WHERE floor_id=:floor_id AND user_id=:user_id';
		$req = $link->prepare($sql);
		$req->bindValue(':floor_id', $floorid, PDO::PARAM_INT);
		$req->bindValue(':user_id',  $userid,  PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		$do = $req->fetch(PDO::FETCH_OBJ);
		
		if($status == 1){
			$sql = 'UPDATE user_floor
			        SET floor_allowed = 1
			        WHERE user_id=:user_id AND floor_id=:floor_id';
			$req = $link->prepare($sql);
			$req->bindValue(':user_id',  $userid,  PDO::PARAM_INT);
			$req->bindValue(':floor_id', $floorid, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
		}
		else {
			$sql = 'UPDATE user_floor
			        SET floor_allowed = 0
			        WHERE user_id=:user_id AND floor_id=:floor_id';
			$req = $link->prepare($sql);
			$req->bindValue(':user_id',  $userid,  PDO::PARAM_INT);
			$req->bindValue(':floor_id', $floorid, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
		}
	}
	

	function confDbListLocal(){
		$socket = new Socket();
		$socket->send('backup_db_list_local');
		$listDb = $socket->receive();
		if (!empty ($listDb)){
			return json_decode($listDb);
		}
		else{
			return NULL;			
		}
	}
 	
	function confDbCreateLocal(){
		$socket = new Socket();
		$socket->send('backup_db_create_local');
	}
 	
	function confDbRemoveLocal($filename){
		$socket = new Socket();
		$socket->send('backup_db_remove_local', $filename);
	}
 	
	function confDbRestoreLocal($filename){
		$socket = new Socket();
		$socket->send('backup_db_restore_local', $filename);
	}
	
	function confDbBackupUSB(){
		$socket = new Socket();
		$socket->send('backup_usb');
		$listBackupUSB = $socket->receive();
		return $listBackupUSB;
	}
	/*** Option ***/
	function confOptionList(){
		$link = Link::get_link('mastercommand');
		$list = array();
		
		$sql = 'SELECT option_id, 
		               if(name'.$this->getLanguage().' = "", name, name'.$this->getLanguage().') as name 
		        FROM optiondef
		        ORDER BY name'.$this->getLanguage();
		$req = $link->prepare($sql);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[$do->option_id] = clone $do;
		}
		
		return $list;
	}
	
	function monitorEnocean() {
		$link = Link::get_link('mastercommand');
		$list = array();
		
		$sql = 'SELECT type, addr_src, addr_dest, eo_value, t_date, daemon_id
		        FROM enocean_log
		        ORDER BY t_date DESC
		        LIMIT 1000';
		$req = $link->prepare($sql);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[] = clone $do;
		}
	
		return $list;
	}
	
	function monitorKnx() {
		$link = Link::get_link('mastercommand');
		$list = array();
		
		$sql = 'SELECT type, addr_src, addr_dest, knx_value, t_date, daemon_id
		        FROM knx_log
		        ORDER BY t_date DESC
		        LIMIT 1000';
		$req = $link->prepare($sql);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[] = clone $do;
		}
		
		return $list;
	}
	
	function monitorIp() {
		$link = Link::get_link('mastercommand');
		$list = array();
		
		$sql = 'SELECT mac_addr, ip_addr, hostname, last_update
		        FROM ip_monitor
		        ORDER BY hostname';
		$req = $link->prepare($sql);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[] = clone $do;
		}
		
		return $list;
	}
	
	function monitorIpRefresh(){
		$socket = new Socket();
		
		$socket->send("monitor_ip");
	}
	
	/********************** User permission **********************/
	
	/**
	 * Set floor order
	 * @param int : user id
	 * @param int : floor id
	 * @param int : -1 ou 1
	 */
	function SetFloorOrder($userid, $floorid, $action) {
		$link = Link::get_link('mastercommand');
		
		if(empty($userid)){
			$userid = $this->getId();
		}
		
		$sql = 'SELECT floor_order, floor_id
		        FROM user_floor
		        WHERE user_id=:user_id AND floor_id=:floor_id';
		$req = $link->prepare($sql);
		$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
		$req->bindValue(':floor_id', $floorid, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		$do = $req->fetch(PDO::FETCH_OBJ);
		
		$order = $do->floor_order + $action;
		if($order >= 1) {
			$sql = 'SELECT floor_order, floor_id
			        FROM user_floor
			        WHERE floor_order=:order AND user_id=:user_id';
			$req = $link->prepare($sql);
			$req->bindValue(':order', $order, PDO::PARAM_INT);
			$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			$do2 = $req->fetch(PDO::FETCH_OBJ);
				
			if(!empty($do2)) {
				$sql = 'UPDATE user_floor
				        SET floor_order=:order
				        WHERE user_id=:user_id AND floor_id=:floor_id';
				$req = $link->prepare($sql);
				$req->bindValue(':order', $do->floor_order + $action, PDO::PARAM_INT);
				$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
				$req->bindValue(':floor_id', $floorid, PDO::PARAM_INT);
				$req->execute() or die (error_log(serialize($req->errorInfo())));
		
				$sql = 'UPDATE  user_floor
				        SET floor_order=:order
				        WHERE floor_id=:floor_id AND user_id=:user_id';
				$req = $link->prepare($sql);
				$req->bindValue(':order', $do2->floor_order - $action, PDO::PARAM_INT);
				$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
				$req->bindValue(':floor_id', $do2->floor_id, PDO::PARAM_INT);
				$req->execute() or die (error_log(serialize($req->errorInfo())));
			}
				
		}
	}
	
	/**
	 * Set room order
	 * @param int : user id
	 * @param int : room id
	 * @param int : -1 ou 1
	 */
	function SetRoomOrder($userid, $roomid, $action){
		$link = Link::get_link('mastercommand');
	
		if(empty($userid)){
			$userid = $this->getId();
		}
	
		$sql = 'SELECT room_order, room_id
		        FROM user_room
		        WHERE user_id=:user_id AND room_id=:room_id';
		$req = $link->prepare($sql);
		$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
		$req->bindValue(':room_id', $roomid, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		$do = $req->fetch(PDO::FETCH_OBJ);
	
		$order = $do->room_order + $action;
		if($order >= 1){
			$sql = 'SELECT room_order, room_id
			        FROM user_room
			        WHERE room_order=:order AND user_id=:user_id';
			$req = $link->prepare($sql);
			$req->bindValue(':order', $order, PDO::PARAM_INT);
			$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			$do2 = $req->fetch(PDO::FETCH_OBJ);
	
			if(!empty($do2)){
				$sql = 'UPDATE user_room
				        SET room_order=:order
				        WHERE user_id=:user_id AND room_id=:room_id';
				$req = $link->prepare($sql);
				$req->bindValue(':order', $do->room_order + $action, PDO::PARAM_INT);
				$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
				$req->bindValue(':room_id', $roomid, PDO::PARAM_INT);
				$req->execute() or die (error_log(serialize($req->errorInfo())));
					
				$sql = 'UPDATE  user_room
				        SET room_order=:order
				        WHERE room_id=:room_id AND user_id=:user_id';
				$req = $link->prepare($sql);
				$req->bindValue(':order', $do2->room_order - $action, PDO::PARAM_INT);
				$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
				$req->bindValue(':room_id', $do2->room_id, PDO::PARAM_INT);
				$req->execute() or die (error_log(serialize($req->errorInfo())));
			}
		}
	}
	
	/**
	 * Set device order
	 * @param int : user id
	 * @param int : device id
	 * @param int : -1 ou 1
	 */
	function SetDeviceOrder($userid, $deviceid, $action){
		$link = Link::get_link('mastercommand');
	
		if(empty($userid)){
			$userid = $this->getId();
		}
		
		$sql = 'SELECT  device_order, room_device_id
		        FROM user_device
		        WHERE user_id=:user_id AND room_device_id=:room_device_id';
		$req = $link->prepare($sql);
		$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
		$req->bindValue(':room_device_id', $deviceid, PDO::PARAM_INT);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		$do = $req->fetch(PDO::FETCH_OBJ);
	
		$order = $do->device_order + $action;
		if($order >= 1) {
			$sql = 'SELECT device_order, room_device_id
			        FROM user_device
			        WHERE device_order=:order AND user_id=:user_id';
			$req = $link->prepare($sql);
			$req->bindValue(':order', $order, PDO::PARAM_INT);
			$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			$do2 = $req->fetch(PDO::FETCH_OBJ);
				
			if(!empty($do2)){
				$sql = 'UPDATE user_device
				        SET device_order=:order
				        WHERE user_id=:user_id AND room_device_id=:room_device_id';
				$req = $link->prepare($sql);
				$req->bindValue(':order', $do->device_order + $action, PDO::PARAM_INT);
				$req->bindValue(':user_id', $userid, PDO::PARAM_INT);
				$req->bindValue(':room_device_id', $deviceid, PDO::PARAM_INT);
				$req->execute() or die (error_log(serialize($req->errorInfo())));
	
				$sql = 'UPDATE  user_device
				        SET device_order=:order
				        WHERE room_device_id=:room_device_id AND user_id=:user_id';
				$req = $link->prepare($sql);
				$req->bindValue(':order', $do2->device_order - $action, PDO::PARAM_INT);
				$req->bindValue(':user_id', $this->getId(), PDO::PARAM_INT);
				$req->bindValue(':room_device_id', $do2->room_device_id, PDO::PARAM_INT);
				$req->execute() or die (error_log(serialize($req->errorInfo())));
			}
		}
	}
	
	/*** KNX action ***/
	function knx_write_l($daemon, $addr, $value=0){
		$socket = new Socket();
		$tab = array(
			'daemon' => $daemon,
			'addr'   => $addr,
			'value'  => $value
		);
		
		$socket->send('knx_write_l', $tab);
	}
	
	function knx_write_s($daemon, $addr, $value=0){
		$socket = new Socket();
		$tab = array(
			'daemon' => $daemon,
			'addr'   => $addr,
			'value'  => $value
		);
		
		$socket->send('knx_write_s', $tab);
	}
	
	function knx_read($daemon, $addr){
		$socket = new Socket();
		$tab = array(
			'daemon' => $daemon,
			'addr'   => $addr
		);
		
		$socket->send('knx_read', $tab);
	}
	
	/*** KNX log ***/
	function confKnxAddrList(){
		$link = Link::get_link('mastercommand');
		$list = array();
		
		$sql = 'SELECT DISTINCT(addr_src) as addr_src
		        FROM knx_log';
		$req = $link->prepare($sql);
		$req->execute() or die (error_log(serialize($req->errorInfo())));
		while ($do = $req->fetch(PDO::FETCH_OBJ)) {
			$list[] = $do->addr_src;
		}
		
		return $list;
	}
}

?>
